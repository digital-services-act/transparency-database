#!/usr/bin/env python3
"""Download a global daily archive and convert its CSV parts to Parquet.

Run from any directory with:

    python3 /path/to/pipeline/pipeline.py [--date YYYY-MM-DD|yesterday]

Requires a PostgreSQL driver (psycopg or psycopg2) and PyArrow to be installed
in the Python environment used to run this script.
"""

from __future__ import annotations

import argparse
import csv
import hashlib
import json
import os
import re
import shutil
import stat
import subprocess
import sys
import zipfile
from concurrent.futures import ThreadPoolExecutor, as_completed
from datetime import date, timedelta
from pathlib import Path, PurePosixPath
from typing import Any, Sequence
from urllib.error import HTTPError, URLError
from urllib.parse import urlsplit
from urllib.request import Request, urlopen


CHUNK_SIZE = 8 * 1024 * 1024
PARQUET_BATCH_SIZE = 64 * 1024
CSV_BLOCK_SIZE = 16 * 1024 * 1024
DATABASE_TIMEOUT_SECONDS = 10
DOWNLOAD_TIMEOUT_SECONDS = 60


class PipelineError(RuntimeError):
    """An expected error that should be reported without a traceback."""


def warn(message: str) -> None:
    print(f"WARNING: {message}", file=sys.stderr, flush=True)


def read_dotenv(path: Path) -> dict[str, str]:
    """Read the simple KEY=value forms used by the Laravel .env file."""
    values: dict[str, str] = {}
    if not path.is_file():
        return values

    try:
        lines = path.read_text(encoding="utf-8").splitlines()
    except OSError as exc:
        raise PipelineError(f"Could not read {path}: {exc}") from exc

    for line in lines:
        stripped = line.strip()
        if not stripped or stripped.startswith("#"):
            continue
        if stripped.startswith("export "):
            stripped = stripped[7:].lstrip()
        key, separator, value = stripped.partition("=")
        if not separator:
            continue
        key = key.strip()
        value = value.strip()
        if not key:
            continue

        if value and value[0] in ("'", '"'):
            quote = value[0]
            closing_quote = None
            escaped = False
            for index in range(1, len(value)):
                character = value[index]
                if character == quote and not escaped:
                    closing_quote = index
                    break
                if character == "\\" and not escaped:
                    escaped = True
                else:
                    escaped = False
            if closing_quote is not None:
                remainder = value[closing_quote + 1 :].strip()
                if not remainder or remainder.startswith("#"):
                    value = value[1:closing_quote]
                    if quote == '"':
                        value = re.sub(r'\\(["\\])', r"\1", value)
                else:
                    value = value.split("#", 1)[0].strip()
            else:
                value = value.split("#", 1)[0].strip()
        else:
            value = value.split("#", 1)[0].strip()
        values[key] = value
    return values


def load_settings(project_root: Path) -> dict[str, str]:
    names = (
        "POSTGRESQL_ADDON_DB",
        "POSTGRESQL_ADDON_DIRECT_HOST",
        "POSTGRESQL_ADDON_DIRECT_PORT",
        "POSTGRESQL_ADDON_USER",
        "POSTGRESQL_ADDON_PASSWORD",
        "POSTGRESQL_ADDON_HOST",
        "POSTGRESQL_ADDON_PORT",
        "APP_URL",
    )
    dotenv_values = read_dotenv(project_root / ".env")
    return {
        name: os.environ.get(name) or dotenv_values.get(name, "")
        for name in names
    }


def parse_archive_date(raw_date: str) -> date:
    if raw_date.strip().lower() == "yesterday":
        return date.today() - timedelta(days=1)
    try:
        parsed = date.fromisoformat(raw_date)
    except ValueError as exc:
        raise PipelineError("--date must be 'yesterday' or a date in YYYY-MM-DD format.") from exc
    if parsed.isoformat() != raw_date:
        raise PipelineError("--date must be 'yesterday' or a date in YYYY-MM-DD format.")
    return parsed


def worker_count() -> int:
    return max(1, (os.cpu_count() or 1) // 2)


def load_database_driver() -> Any:
    try:
        import psycopg  # type: ignore[import-not-found]

        return psycopg
    except ImportError:
        try:
            import psycopg2  # type: ignore[import-not-found]

            return psycopg2
        except ImportError as exc:
            raise PipelineError(
                "No PostgreSQL driver is installed. Install psycopg or psycopg2 in this Python environment."
            ) from exc


def load_pyarrow() -> tuple[Any, Any, Any]:
    try:
        import pyarrow as pa
        import pyarrow.csv as arrow_csv
        import pyarrow.parquet as arrow_parquet
    except ImportError as exc:
        raise PipelineError(
            "PyArrow is not installed. Install pyarrow in this Python environment to write Parquet files."
        ) from exc
    return pa, arrow_csv, arrow_parquet


def database_candidates(settings: dict[str, str]) -> list[tuple[str, str, int]]:
    candidates: list[tuple[str, str, int]] = []
    seen: set[tuple[str, int]] = set()
    for label, host_key, port_key in (
        ("direct", "POSTGRESQL_ADDON_DIRECT_HOST", "POSTGRESQL_ADDON_DIRECT_PORT"),
        ("fallback", "POSTGRESQL_ADDON_HOST", "POSTGRESQL_ADDON_PORT"),
    ):
        host = settings.get(host_key, "").strip()
        raw_port = settings.get(port_key, "").strip()
        if not host or not raw_port:
            continue
        try:
            port = int(raw_port)
        except ValueError:
            warn(f"Skipping the {label} PostgreSQL connection because its port is invalid.")
            continue
        if not 1 <= port <= 65535:
            warn(f"Skipping the {label} PostgreSQL connection because its port is out of range.")
            continue
        if (host, port) not in seen:
            candidates.append((label, host, port))
            seen.add((host, port))
    return candidates


def connect_database(settings: dict[str, str], driver: Any) -> Any:
    required = (
        "POSTGRESQL_ADDON_DB",
        "POSTGRESQL_ADDON_USER",
        "POSTGRESQL_ADDON_PASSWORD",
    )
    missing = [name for name in required if not settings.get(name, "").strip()]
    if missing:
        raise PipelineError("Missing PostgreSQL environment setting(s): " + ", ".join(missing))

    candidates = database_candidates(settings)
    if not candidates:
        raise PipelineError(
            "No complete PostgreSQL host and port were found for either the direct or fallback connection."
        )

    failures: list[str] = []
    for index, (label, host, port) in enumerate(candidates):
        try:
            return driver.connect(
                host=host,
                port=port,
                dbname=settings["POSTGRESQL_ADDON_DB"],
                user=settings["POSTGRESQL_ADDON_USER"],
                password=settings["POSTGRESQL_ADDON_PASSWORD"],
                sslmode="prefer",
                connect_timeout=DATABASE_TIMEOUT_SECONDS,
                options="-c search_path=public",
            )
        except Exception as exc:
            failures.append(f"{label}: {type(exc).__name__}")
            if index + 1 < len(candidates):
                warn(f"The {label} PostgreSQL connection failed; trying the fallback details.")

    raise PipelineError("Could not connect to PostgreSQL using the configured details (" + "; ".join(failures) + ").")


def find_archive_row(connection: Any, archive_date: date) -> tuple[int, str]:
    query = """
        SELECT id, url
        FROM public.day_archives
        WHERE \"date\" = %s::date
          AND platform_id IS NULL
          AND completed_at IS NOT NULL
          AND url IS NOT NULL
          AND BTRIM(url) <> ''
        ORDER BY id DESC
        LIMIT 1
    """
    try:
        with connection.cursor() as cursor:
            cursor.execute(query, (archive_date.isoformat(),))
            row = cursor.fetchone()
    except Exception as exc:
        raise PipelineError(f"Could not query public.day_archives ({type(exc).__name__}).") from exc
    if row is None:
        raise PipelineError(
            f"No completed full global archive with a URL exists for {archive_date.isoformat()}."
        )
    return int(row[0]), str(row[1])


def make_download_url(app_url: str, archive_id: int) -> str:
    parts = urlsplit(app_url.strip())
    if parts.scheme not in ("http", "https") or not parts.netloc:
        raise PipelineError("APP_URL is missing or is not a valid HTTP or HTTPS URL.")
    base = app_url.strip().rstrip("/")
    return f"{base}/explore-data/download-file/{archive_id}/full"


def make_laravel_signed_url(project_root: Path, filename: str) -> str:
    """Ask the local Laravel application to sign a Cellar URL when CAS blocks HTTP."""
    php = shutil.which("php")
    if not php:
        raise PipelineError("The Laravel CLI fallback requires the php executable on PATH.")

    php_code = r'''require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo \Illuminate\Support\Facades\Storage::disk("s3ds")->temporaryUrl(
    $argv[1],
    \Illuminate\Support\Carbon::now()->addMinutes(60)
);'''
    try:
        result = subprocess.run(
            [php, "-r", php_code, filename],
            cwd=project_root,
            capture_output=True,
            text=True,
            timeout=30,
            check=False,
        )
    except (OSError, subprocess.TimeoutExpired) as exc:
        raise PipelineError(f"Could not start the Laravel URL signer ({type(exc).__name__}).") from exc

    if result.returncode != 0:
        raise PipelineError("Laravel could not generate a signed Cellar URL. Check its CLI bootstrap and storage configuration.")

    signed_url = result.stdout.strip()
    parts = urlsplit(signed_url)
    if parts.scheme not in ("http", "https") or not parts.netloc or not parts.query:
        raise PipelineError("Laravel did not return a valid signed Cellar URL.")
    if PurePosixPath(parts.path).name != filename:
        raise PipelineError("Laravel signed a URL for an unexpected archive filename.")
    return signed_url


def download_archive(download_url: str, destination: Path) -> None:
    request = Request(
        download_url,
        headers={
            "Accept": "application/zip,application/octet-stream,*/*",
            "User-Agent": "transparency-database-pipeline/1.0",
        },
    )
    temporary_path = destination.with_name(destination.name + ".part")
    try:
        with urlopen(request, timeout=DOWNLOAD_TIMEOUT_SECONDS) as response:
            status = response.getcode()
            content_type = response.headers.get("Content-Type", "").lower()
            if status != 200:
                raise PipelineError(f"The archive download endpoint returned HTTP {status}.")
            if "text/html" in content_type:
                raise PipelineError("The archive download endpoint returned an HTML page instead of a ZIP file.")
            with temporary_path.open("wb") as output:
                while True:
                    chunk = response.read(CHUNK_SIZE)
                    if not chunk:
                        break
                    output.write(chunk)
    except HTTPError as exc:
        temporary_path.unlink(missing_ok=True)
        raise PipelineError(f"The archive download endpoint returned HTTP {exc.code}.") from exc
    except URLError as exc:
        temporary_path.unlink(missing_ok=True)
        reason_name = type(exc.reason).__name__
        raise PipelineError(f"Could not reach the archive download endpoint ({reason_name}).") from exc
    except PipelineError:
        temporary_path.unlink(missing_ok=True)
        raise
    except Exception as exc:
        temporary_path.unlink(missing_ok=True)
        raise PipelineError(f"Archive download failed ({type(exc).__name__}).") from exc

    if not zipfile.is_zipfile(temporary_path):
        temporary_path.unlink(missing_ok=True)
        raise PipelineError("The downloaded response is not a valid ZIP archive.")
    temporary_path.replace(destination)


def safe_extract_zip(archive_path: Path, destination: Path) -> None:
    """Extract ZIP entries while rejecting path traversal and symlinks."""
    destination.mkdir(parents=True, exist_ok=True)
    root = destination.resolve()
    try:
        with zipfile.ZipFile(archive_path) as archive:
            for member in archive.infolist():
                normalized_name = member.filename.replace("\\", "/")
                relative_path = PurePosixPath(normalized_name)
                if (
                    relative_path.is_absolute()
                    or not relative_path.parts
                    or any(part in ("..", "") for part in relative_path.parts)
                    or relative_path.parts[0].endswith(":")
                ):
                    raise PipelineError(f"Unsafe ZIP member path in {archive_path.name}.")

                mode = member.external_attr >> 16
                if stat.S_ISLNK(mode):
                    raise PipelineError(f"ZIP archive {archive_path.name} contains a symbolic link.")

                target = destination.joinpath(*relative_path.parts)
                if os.path.commonpath((str(root), str(target.resolve()))) != str(root):
                    raise PipelineError(f"Unsafe ZIP member path in {archive_path.name}.")
                if member.is_dir():
                    target.mkdir(parents=True, exist_ok=True)
                    continue

                target.parent.mkdir(parents=True, exist_ok=True)
                with archive.open(member, "r") as source, target.open("wb") as output:
                    shutil.copyfileobj(source, output, length=CHUNK_SIZE)
    except zipfile.BadZipFile as exc:
        raise PipelineError(f"{archive_path.name} is not a valid ZIP archive.") from exc
    except OSError as exc:
        raise PipelineError(f"Could not extract {archive_path.name}: {exc}") from exc


def csv_headers(csv_path: Path) -> tuple[str, ...]:
    try:
        with csv_path.open("r", encoding="utf-8-sig", newline="") as source:
            header = next(csv.reader(source), None)
    except (OSError, UnicodeError, csv.Error) as exc:
        raise PipelineError(f"Could not read the CSV header in {csv_path.name}: {exc}") from exc
    if not header or any(not column for column in header):
        raise PipelineError(f"The CSV file {csv_path.name} has no usable header row.")
    if len(set(header)) != len(header):
        raise PipelineError(f"The CSV file {csv_path.name} contains duplicate column names.")
    return tuple(header)


def convert_csv_to_parquet(
    csv_path: Path,
    expected_headers: tuple[str, ...],
    arrow_csv: Any,
    arrow_parquet: Any,
    arrow: Any,
) -> tuple[Path, int]:
    parquet_path = csv_path.with_suffix(".parquet")
    temporary_path = parquet_path.with_name(parquet_path.name + ".part")
    reader = None
    writer = None
    row_count = 0
    try:
        reader = arrow_csv.open_csv(
            str(csv_path),
            read_options=arrow_csv.ReadOptions(use_threads=False, block_size=CSV_BLOCK_SIZE),
            parse_options=arrow_csv.ParseOptions(delimiter=","),
            convert_options=arrow_csv.ConvertOptions(
                column_types={name: arrow.string() for name in expected_headers},
                strings_can_be_null=False,
            ),
        )
        if tuple(reader.schema.names) != expected_headers:
            raise PipelineError(f"CSV columns in {csv_path.name} do not match the archive's other CSV files.")

        writer = arrow_parquet.ParquetWriter(
            str(temporary_path),
            reader.schema,
            compression="zstd",
            use_dictionary=True,
        )
        while True:
            try:
                batch = reader.read_next_batch()
            except StopIteration:
                break
            writer.write_batch(batch, row_group_size=PARQUET_BATCH_SIZE)
            row_count += batch.num_rows
        writer.close()
        writer = None
        temporary_path.replace(parquet_path)
        return parquet_path, row_count
    except PipelineError:
        raise
    except Exception as exc:
        raise PipelineError(f"Could not convert {csv_path.name} to Parquet ({type(exc).__name__}: {exc}).") from exc
    finally:
        if writer is not None:
            writer.close()
        temporary_path.unlink(missing_ok=True)


def parquet_schema(path: Path, arrow_parquet: Any) -> Any:
    try:
        return arrow_parquet.ParquetFile(str(path)).schema_arrow
    except Exception as exc:
        raise PipelineError(f"Could not read the Parquet schema from {path.name}: {exc}") from exc


def merge_parquet_files(
    input_paths: Sequence[Path],
    output_path: Path,
    expected_schema: Any,
    arrow_parquet: Any,
) -> Path:
    if not input_paths:
        raise PipelineError(f"No input Parquet files were found for {output_path.name}.")

    temporary_path = output_path.with_name(output_path.name + ".part")
    writer = None
    try:
        writer = arrow_parquet.ParquetWriter(
            str(temporary_path),
            expected_schema,
            compression="zstd",
            use_dictionary=True,
        )
        for input_path in input_paths:
            parquet_file = arrow_parquet.ParquetFile(str(input_path))
            if not parquet_file.schema_arrow.equals(expected_schema, check_metadata=False):
                raise PipelineError(f"Parquet schema mismatch in {input_path.name}.")
            for batch in parquet_file.iter_batches(
                batch_size=PARQUET_BATCH_SIZE,
                use_threads=True,
            ):
                writer.write_batch(batch, row_group_size=PARQUET_BATCH_SIZE)
        writer.close()
        writer = None
        temporary_path.replace(output_path)
        return output_path
    except PipelineError:
        raise
    except Exception as exc:
        raise PipelineError(f"Could not combine Parquet files into {output_path.name} ({type(exc).__name__}: {exc}).") from exc
    finally:
        if writer is not None:
            writer.close()
        temporary_path.unlink(missing_ok=True)


def sha1_file(path: Path) -> str:
    digest = hashlib.sha1()
    try:
        with path.open("rb") as source:
            while chunk := source.read(CHUNK_SIZE):
                digest.update(chunk)
    except OSError as exc:
        raise PipelineError(f"Could not read {path.name} to calculate its SHA1: {exc}") from exc
    return digest.hexdigest()


def upload_parquet_artifacts(
    project_root: Path,
    parquet_path: Path,
    checksum_path: Path,
) -> tuple[str, str]:
    """Upload the Parquet file and checksum through Laravel's configured Cellar disk."""
    php = shutil.which("php")
    if not php:
        raise PipelineError("Uploading to Cellar requires the php executable on PATH.")

    php_code = r'''require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$disk = \Illuminate\Support\Facades\Storage::disk("s3ds");
$upload = static function (string $localPath, string $remoteName) use ($disk): void {
    $stream = fopen($localPath, "rb");
    if ($stream === false) {
        throw new RuntimeException("Could not open upload source.");
    }
    try {
        if (! $disk->put($remoteName, $stream, ["visibility" => "private"])) {
            throw new RuntimeException("Cellar upload failed.");
        }
    } finally {
        fclose($stream);
    }
};
$upload($argv[1], $argv[2]);
$upload($argv[3], $argv[4]);
$config = config("filesystems.disks.s3ds");
$baseUrl = rtrim((string) ($config["url"] ?? ""), "/")."/".trim((string) ($config["bucket"] ?? ""), "/")."/";
echo json_encode([
    "parquet_url" => $baseUrl.$argv[2],
    "parquet_sha1_url" => $baseUrl.$argv[4],
], JSON_UNESCAPED_SLASHES);'''

    try:
        result = subprocess.run(
            [
                php,
                "-r",
                php_code,
                str(parquet_path),
                parquet_path.name,
                str(checksum_path),
                checksum_path.name,
            ],
            cwd=project_root,
            capture_output=True,
            text=True,
            timeout=60 * 30,
            check=False,
        )
    except (OSError, subprocess.TimeoutExpired) as exc:
        raise PipelineError(f"Could not start the Laravel Cellar upload ({type(exc).__name__}).") from exc

    if result.returncode != 0:
        raise PipelineError("Laravel could not upload the Parquet artifacts to Cellar. Check Laravel and Cellar logs.")

    try:
        upload_result = json.loads(result.stdout.strip())
        parquet_url = str(upload_result["parquet_url"])
        checksum_url = str(upload_result["parquet_sha1_url"])
    except (json.JSONDecodeError, KeyError, TypeError) as exc:
        raise PipelineError("Laravel uploaded the Parquet artifacts but did not return their storage URLs.") from exc

    for url, expected_name in (
        (parquet_url, parquet_path.name),
        (checksum_url, checksum_path.name),
    ):
        parts = urlsplit(url)
        if parts.scheme not in ("http", "https") or not parts.netloc or PurePosixPath(parts.path).name != expected_name:
            raise PipelineError("Laravel returned an invalid Cellar URL for a Parquet artifact.")
    return parquet_url, checksum_url


def update_archive_parquet_fields(
    settings: dict[str, str],
    database_driver: Any,
    archive_id: int,
    archive_date: date,
    parquet_size: int,
    parquet_sha1: str,
    parquet_url: str,
    parquet_sha1_url: str,
    total_parquet: int,
) -> None:
    connection = connect_database(settings, database_driver)
    query = """
        UPDATE public.day_archives
        SET parquet_size = %s,
            parquet_sha1 = %s,
            parquet_url = %s,
            parquet_sha1_url = %s,
            total_parquet = %s
        WHERE id = %s
          AND \"date\" = %s::date
          AND platform_id IS NULL
          AND completed_at IS NOT NULL
    """
    try:
        with connection.cursor() as cursor:
            cursor.execute(
                query,
                (
                    parquet_size,
                    parquet_sha1,
                    parquet_url,
                    parquet_sha1_url,
                    total_parquet,
                    archive_id,
                    archive_date.isoformat(),
                ),
            )
            if cursor.rowcount != 1:
                raise PipelineError(f"Could not update global day archive row {archive_id} with Parquet metadata.")
        connection.commit()
    except PipelineError:
        connection.rollback()
        raise
    except Exception as exc:
        connection.rollback()
        raise PipelineError(f"Could not save Parquet metadata to public.day_archives ({type(exc).__name__}).") from exc
    finally:
        connection.close()


def run_pipeline(raw_date: str) -> Path:
    archive_date = parse_archive_date(raw_date)
    date_text = archive_date.isoformat()

    script_directory = Path(__file__).resolve().parent
    project_root = script_directory.parent
    bucket_data = project_root / "bucket-data"
    output_directory = bucket_data / f"pipeline-{date_text}"

    if not bucket_data.is_dir():
        raise PipelineError(f"The required bucket-data directory does not exist: {bucket_data}")
    if output_directory.exists():
        raise PipelineError(f"The output directory already exists: {output_directory}")

    settings = load_settings(project_root)
    app_url = settings.get("APP_URL", "").strip()
    if not app_url:
        raise PipelineError("APP_URL was not found in the environment or ../.env.")
    if not urlsplit(app_url).netloc or urlsplit(app_url).scheme not in ("http", "https"):
        raise PipelineError("APP_URL is not a valid HTTP or HTTPS URL.")

    database_driver = load_database_driver()
    arrow, arrow_csv, arrow_parquet = load_pyarrow()
    workers = worker_count()
    print(f"Archive date: {date_text}")
    print(f"Using {workers} worker(s) for extraction, conversion, and part merging.")

    connection = connect_database(settings, database_driver)
    try:
        archive_id, stored_archive_url = find_archive_row(connection, archive_date)
    finally:
        connection.close()
    print(f"Selected completed global day archive row {archive_id}.")

    download_url = make_download_url(app_url, archive_id)
    try:
        output_directory.mkdir()
    except FileExistsError as exc:
        raise PipelineError(f"The output directory already exists: {output_directory}") from exc
    except OSError as exc:
        raise PipelineError(f"Could not create the output directory {output_directory}: {exc}") from exc

    archive_name = f"sor-global-{date_text}-full.zip"
    archive_path = output_directory / archive_name
    print(f"Downloading {archive_name} through the application download endpoint...")
    try:
        download_archive(download_url, archive_path)
    except PipelineError as endpoint_error:
        warn(f"The application download endpoint could not provide the archive ({endpoint_error}); trying Laravel's local storage signer.")
        stored_filename = PurePosixPath(urlsplit(stored_archive_url).path).name
        if stored_filename != archive_name:
            raise PipelineError(
                f"The database URL filename {stored_filename!r} does not match the expected archive name {archive_name!r}."
            ) from endpoint_error
        signed_url = make_laravel_signed_url(project_root, stored_filename)
        try:
            download_archive(signed_url, archive_path)
        except PipelineError as signed_error:
            raise PipelineError(
                f"The archive could not be downloaded through the application endpoint ({endpoint_error}) "
                f"or Laravel's local storage signer ({signed_error})."
            ) from signed_error
    print(f"Downloaded and validated {archive_name}.")

    safe_extract_zip(archive_path, output_directory)
    part_pattern = re.compile(
        rf"^sor-global-{re.escape(date_text)}-full-(\d+)\.csv\.zip$"
    )
    inner_archives: list[tuple[int, Path]] = []
    for path in output_directory.iterdir():
        match = part_pattern.match(path.name)
        if match and path.is_file():
            inner_archives.append((int(match.group(1)), path))
    inner_archives.sort(key=lambda item: item[0])
    if not inner_archives:
        raise PipelineError("The downloaded ZIP did not contain any full CSV part archives.")

    print(f"Extracting {len(inner_archives)} CSV part archive(s)...")
    part_directories: list[tuple[int, Path]] = []
    with ThreadPoolExecutor(max_workers=workers) as executor:
        futures = {
            executor.submit(safe_extract_zip, archive, output_directory / archive.name.removesuffix(".zip")): (
                part_number,
                output_directory / archive.name.removesuffix(".zip"),
            )
            for part_number, archive in inner_archives
        }
        for future in as_completed(futures):
            try:
                future.result()
                part_directories.append(futures[future])
            except Exception as exc:
                raise PipelineError(f"Could not extract a CSV part archive ({type(exc).__name__}: {exc}).") from exc
    part_directories.sort(key=lambda item: item[0])

    csv_groups: list[tuple[int, str, list[Path]]] = []
    for part_number, directory in part_directories:
        csv_files = sorted(path for path in directory.rglob("*.csv") if path.is_file())
        if not csv_files:
            raise PipelineError(f"The part archive {directory.name}.zip did not contain any CSV files.")
        csv_groups.append((part_number, directory.name.removesuffix(".csv"), csv_files))

    all_csv_files = [csv_path for _, _, csv_files in csv_groups for csv_path in csv_files]
    headers = csv_headers(all_csv_files[0])
    print(f"Converting {len(all_csv_files)} CSV file(s) to Parquet...")
    conversion_tasks = []
    with ThreadPoolExecutor(max_workers=workers) as executor:
        for csv_path in all_csv_files:
            conversion_tasks.append(
                executor.submit(
                    convert_csv_to_parquet,
                    csv_path,
                    headers,
                    arrow_csv,
                    arrow_parquet,
                    arrow,
                )
            )
        converted = 0
        for future in as_completed(conversion_tasks):
            try:
                future.result()
            except Exception as exc:
                raise PipelineError(f"CSV-to-Parquet conversion failed ({type(exc).__name__}: {exc}).") from exc
            converted += 1
            if converted % 100 == 0 or converted == len(conversion_tasks):
                print(f"Converted {converted}/{len(conversion_tasks)} CSV file(s).", flush=True)

    first_csv_parquet = all_csv_files[0].with_suffix(".parquet")
    expected_schema = parquet_schema(first_csv_parquet, arrow_parquet)
    part_parquets: list[Path] = []
    print(f"Combining CSV Parquet files into {len(csv_groups)} archive part(s)...")

    def merge_one_part(group: tuple[int, str, list[Path]]) -> Path:
        _, part_stem, csv_files = group
        parquet_files = [csv_path.with_suffix(".parquet") for csv_path in csv_files]
        output_path = output_directory / f"{part_stem}.parquet"
        return merge_parquet_files(parquet_files, output_path, expected_schema, arrow_parquet)

    with ThreadPoolExecutor(max_workers=workers) as executor:
        futures = {executor.submit(merge_one_part, group): group[0] for group in csv_groups}
        for future in as_completed(futures):
            try:
                part_parquets.append(future.result())
            except Exception as exc:
                raise PipelineError(f"Could not combine an archive part ({type(exc).__name__}: {exc}).") from exc
    part_parquets.sort(key=lambda path: int(path.stem.rsplit("-", 1)[1]))

    final_name = f"sor-global-{date_text}-full.parquet"
    final_path = output_directory / final_name
    print(f"Combining {len(part_parquets)} archive part(s) into {final_name}...")
    merge_parquet_files(part_parquets, final_path, expected_schema, arrow_parquet)

    total_parquet = arrow_parquet.ParquetFile(str(final_path)).metadata.num_rows
    parquet_size = final_path.stat().st_size
    parquet_sha1 = sha1_file(final_path)
    checksum_path = final_path.with_name(final_path.name + ".sha1")
    checksum_path.write_text(f"{parquet_sha1}  {final_path.name}", encoding="ascii")

    print(f"Uploading {final_name} and its SHA1 file to Cellar...")
    parquet_url, parquet_sha1_url = upload_parquet_artifacts(project_root, final_path, checksum_path)
    update_archive_parquet_fields(
        settings,
        database_driver,
        archive_id,
        archive_date,
        parquet_size,
        parquet_sha1,
        parquet_url,
        parquet_sha1_url,
        total_parquet,
    )
    print(
        f"Pipeline completed: {final_path} "
        f"(rows={total_parquet}, size={parquet_size}, sha1={parquet_sha1}); "
        f"uploaded and saved metadata to day_archives row {archive_id}."
    )
    return final_path


def main() -> int:
    parser = argparse.ArgumentParser(description="Download and convert a global daily archive to Parquet.")
    parser.add_argument(
        "--date",
        default="yesterday",
        help="Archive date in YYYY-MM-DD format (default: yesterday).",
    )
    arguments = parser.parse_args()

    try:
        run_pipeline(arguments.date)
    except PipelineError as exc:
        warn(str(exc))
        return 1
    except KeyboardInterrupt:
        warn("Pipeline interrupted. The partial output directory was left in place.")
        return 130
    except Exception as exc:
        warn(f"Pipeline failed ({type(exc).__name__}: {exc}). The partial output directory was left in place.")
        return 1
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
