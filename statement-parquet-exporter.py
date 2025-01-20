import os
import datetime
import gc
import multiprocessing
import concurrent.futures
import argparse
import hashlib
from typing import Optional, Tuple
from concurrent.futures import ThreadPoolExecutor
from pathlib import Path

import pandas as pd
import pyarrow as pa
import pyarrow.parquet as pq
import boto3
from sqlalchemy import create_engine, text
import sys


class StatementExporter:
    def __init__(self, verbose: bool = False):
        """Initialize the exporter"""
        self.verbose = verbose
        
        # For a 96 vCPU machine, use about 1/3 of the cores for optimal throughput
        self.max_workers = 32  # Fixed number for 96 vCPU instance
        
        # Setup database connection
        db_host = os.getenv('DB_HOST_READER', 'localhost')
        db_port = os.getenv('DB_PORT', '3306')
        db_database = os.getenv('DB_DATABASE', '')
        db_username = os.getenv('DB_USERNAME', '')
        db_password = os.getenv('DB_PASSWORD', '')
        
        connection_string = f'mysql+pymysql://{db_username}:{db_password}@{db_host}:{db_port}/{db_database}'
        
        self.engine = create_engine(
            connection_string,
            pool_size=self.max_workers,      # Match pool to worker count
            max_overflow=self.max_workers,    # Allow double capacity if needed
            pool_timeout=60,    
            pool_pre_ping=True,
            pool_recycle=3600,
            execution_options={
                "stream_results": True,
                "max_row_buffer": 100000  # Buffer size for streaming
            }
        )
        self.CHUNK_SIZE = 100000
        
        # Initialize S3 client
        self.s3_client = boto3.client(
            's3',
            aws_access_key_id=os.getenv('AWS_DS_ACCESS_KEY_ID'),
            aws_secret_access_key=os.getenv('AWS_DS_SECRET_ACCESS_KEY')
        )
        self.s3_bucket = os.getenv('AWS_PARQUET_BUCKET')
        
        # Load platform data into memory
        self._echo("Loading platform data...")
        with self.engine.connect() as conn:
            platforms_df = pd.read_sql(
                "SELECT id, name, vlop FROM platforms",
                conn
            )
        # Create separate lookups for name and vlop status
        self.platform_lookup = dict(zip(platforms_df.id, platforms_df.name))
        self.platform_vlop_lookup = dict(zip(platforms_df.id, platforms_df.vlop))
        self._echo(f"Loaded {len(self.platform_lookup)} platforms")

    def _echo(self, message: str, end: str = "\n", flush: bool = False) -> None:
        """Helper method for verbose output"""
        if self.verbose:
            print(message, end=end, flush=flush)

    def get_first_id_of_date(self, date: datetime.date) -> Optional[int]:
        """Get first ID for a specific date using second-by-second search"""
        start_of_day = datetime.datetime.combine(date, datetime.time.min)
        self._echo(f"Searching for first ID on {date}")
        
        with self.engine.connect() as conn:
            # Check each second in the first minute
            for second in range(60):
                current_time = start_of_day + datetime.timedelta(seconds=second)
                formatted_time = current_time.strftime('%Y-%m-%d %H:%M:%S')
                self._echo(f"Checking time: {formatted_time}")
                
                result = conn.execute(text("""
                    SELECT MIN(id) as min_id 
                    FROM statements 
                    WHERE created_at = :timestamp
                """), {"timestamp": formatted_time}).scalar()
                
                if result:
                    self._echo(f"Found first ID: {result}")
                    return result
                
        self._echo("No ID found in the first minute of the day")
        return None

    def get_last_id_of_date(self, date: datetime.date) -> Optional[int]:
        """Get last ID for a specific date using second-by-second search"""
        end_of_day = datetime.datetime.combine(date, datetime.time.max)
        self._echo(f"Searching for last ID on {date}")
        
        with self.engine.connect() as conn:
            # Check each second in the last minute, going backwards
            for second in range(60):
                current_time = end_of_day - datetime.timedelta(seconds=second)
                formatted_time = current_time.strftime('%Y-%m-%d %H:%M:%S')
                self._echo(f"Checking time: {formatted_time}")
                
                result = conn.execute(text("""
                    SELECT MAX(id) as max_id 
                    FROM statements 
                    WHERE created_at = :timestamp
                """), {"timestamp": formatted_time}).scalar()
                
                if result:
                    self._echo(f"Found last ID: {result}")
                    return result
                
        self._echo("No ID found in the last minute of the day")
        return None

    def process_chunk(self, start_id: int, end_id: int, chunk_number: int, total_chunks: int, date: datetime.date) -> str:
        """Process a chunk of statements and save to parquet"""
        temp_file = os.path.abspath(f"{self.output_dir}/chunk-{date}-{chunk_number:05d}.parquet")
        
        # Skip if chunk already exists and is valid
        if os.path.exists(temp_file):
            try:
                # Verify the chunk is readable
                test_table = pq.read_table(temp_file)
                self._echo(f"Chunk {chunk_number}/{total_chunks} for {date} already exists and is valid, skipping...")
                return temp_file
            except Exception:
                self._echo(f"Existing chunk {chunk_number}/{total_chunks} for {date} is invalid, regenerating...")
                os.remove(temp_file)
        
        self._echo(f"Processing chunk {chunk_number}/{total_chunks} for {date} (IDs {start_id} to {end_id})")
        
        query = text("""
            SELECT s.*
            FROM statements s
            WHERE s.id >= :start_id AND s.id <= :end_id
            ORDER BY s.id
        """)
        
        df = pd.read_sql(query, self.engine, params={"start_id": start_id, "end_id": end_id})
        df['platform_name'] = df['platform_id'].map(self.platform_lookup)
        df['platform_is_vlop'] = df['platform_id'].map(self.platform_vlop_lookup)
        
        # Ensure consistent types
        if 'id' in df.columns:
            df['id'] = df['id'].astype('int64')
        if 'platform_id' in df.columns:
            df['platform_id'] = df['platform_id'].astype('int64')
        if 'platform_name' in df.columns:
            df['platform_name'] = df['platform_name'].astype('string')
        if 'platform_is_vlop' in df.columns:
            df['platform_is_vlop'] = df['platform_is_vlop'].astype('bool')
        
        # Write chunk to parquet
        table = pa.Table.from_pandas(df)
        pq.write_table(table, temp_file, compression='snappy')
        
        del df
        del table
        gc.collect()
        
        return temp_file

    def generate_sha1(self, file_path: str) -> Tuple[str, str]:
        """Generate SHA1 hash for a file and create .sha1 file"""
        sha1_path = f"{file_path}.sha1"
        
        # Generate SHA1 for large files efficiently
        sha1 = hashlib.sha1()
        with open(file_path, 'rb') as f:
            while chunk := f.read(8192):  # Read in 8KB chunks
                sha1.update(chunk)
        
        sha1_hex = sha1.hexdigest()
        
        # Write SHA1 to file
        with open(sha1_path, 'w') as f:
            f.write(sha1_hex)
        
        return sha1_path, sha1_hex

    def upload_to_s3(self, file_path: str, s3_key: str) -> bool:
        """Upload file to S3 using multipart upload for large files"""
        self._echo(f"Uploading {file_path} to s3://{self.s3_bucket}/{s3_key}")
        
        try:
            # Use TransferConfig for optimal large file handling
            config = boto3.s3.transfer.TransferConfig(
                multipart_threshold=1024 * 1024 * 8,  # 8MB
                max_concurrency=10,
                multipart_chunksize=1024 * 1024 * 8,  # 8MB per part
                use_threads=True
            )
            
            # Upload with progress callback if verbose
            if self.verbose:
                file_size = os.path.getsize(file_path)
                def progress_callback(bytes_transferred):
                    percentage = (bytes_transferred * 100) / file_size
                    self._echo(f"\rUpload progress: {percentage:.2f}%", end="", flush=True)
                
                self.s3_client.upload_file(
                    file_path,
                    self.s3_bucket,
                    s3_key,
                    Config=config,
                    Callback=progress_callback
                )
                self._echo("")  # New line after progress
            else:
                self.s3_client.upload_file(
                    file_path,
                    self.s3_bucket,
                    s3_key,
                    Config=config
                )
            return True
        except Exception as e:
            self._echo(f"Error uploading {file_path} to S3: {str(e)}")
            return False

    def check_s3_file_exists(self, s3_key: str) -> bool:
        """Check if file exists in S3 bucket"""
        try:
            self.s3_client.head_object(Bucket=self.s3_bucket, Key=s3_key)
            return True
        except:
            return False

    def export_day(self, date: datetime.date, output_dir: str):
        """Export one day's worth of statements to parquet files"""
        self.output_dir = output_dir
        os.makedirs(output_dir, exist_ok=True)
        
        final_file = os.path.abspath(f"{output_dir}/sor-global-{date}.parquet")
        s3_key = f"sor-global-{date}.parquet"
        
        # If final file exists locally, just generate SHA1 and upload both files
        if os.path.exists(final_file):
            self._echo(f"Final file already exists: {final_file}")
            self._echo("Generating SHA1 and uploading to S3...")
            
            sha1_path, _ = self.generate_sha1(final_file)
            self.upload_to_s3(final_file, s3_key)
            self.upload_to_s3(sha1_path, f"{s3_key}.sha1")
            return
        
        start_time = datetime.datetime.now()
        self._echo(f"Starting export for date: {date}")
        
        first_id = self.get_first_id_of_date(date)
        if not first_id:
            self._echo(f"No starting ID found for {date}")
            return
            
        last_id = self.get_last_id_of_date(date)
        if not last_id:
            self._echo(f"No ending ID found for {date}")
            return
        
        total_records = last_id - first_id + 1
        self._echo(f"Processing {total_records} records from ID {first_id} to {last_id}")
        
        # Split into chunks
        chunks = []
        current_id = first_id
        while current_id <= last_id:
            chunk_end = min(current_id + self.CHUNK_SIZE - 1, last_id)
            chunks.append((current_id, chunk_end))
            current_id = chunk_end + 1
        
        total_chunks = len(chunks)
        self._echo(f"Split into {total_chunks} chunks of {self.CHUNK_SIZE} records each")
        self._echo(f"Using {self.max_workers} parallel workers on {multiprocessing.cpu_count()} available vCPUs")
        
        temp_files = []
        completed_chunks = 0
        
        # Process chunks with parallel processing
        with ThreadPoolExecutor(max_workers=self.max_workers) as executor:
            futures = {
                executor.submit(self.process_chunk, start, end, idx + 1, total_chunks, date): idx 
                for idx, (start, end) in enumerate(chunks)
            }
            
            for future in concurrent.futures.as_completed(futures):
                chunk_idx = futures[future]
                try:
                    temp_file = future.result()
                    temp_files.append(temp_file)
                    completed_chunks += 1
                    
                    # Calculate progress
                    elapsed_time = (datetime.datetime.now() - start_time).total_seconds()
                    progress_rate = completed_chunks / total_chunks
                    if progress_rate > 0:
                        total_estimated_time = elapsed_time / progress_rate
                        remaining_time = total_estimated_time - elapsed_time
                        hours = int(remaining_time // 3600)
                        minutes = int((remaining_time % 3600) // 60)
                        self._echo(f"Completed chunk {completed_chunks}/{total_chunks} ({progress_rate*100:.1f}%) - {hours}h {minutes}m left")
                    
                except Exception as e:
                    self._echo(f"Error processing chunk {chunk_idx + 1}: {str(e)}")
        
        if not temp_files:
            self._echo("No temporary files were created. Something went wrong during processing.")
            return
        
        # Combine all chunks into final file
        self._echo(f"Combining {len(temp_files)} chunks into final file...")
        self._echo("Reading chunks...")
        
        # Read all tables with explicit schema
        tables = []
        schema = None
        for i, temp_file in enumerate(temp_files, 1):
            if i % 50 == 0:
                self._echo(f"Read {i}/{len(temp_files)} chunks...")
            table = pq.read_table(temp_file)
            if schema is None:
                schema = table.schema
            tables.append(table)
        
        self._echo("Concatenating tables...")
        
        # Combine tables
        final_table = pa.concat_tables(tables, promote_options='default')
        
        self._echo("Adding metadata...")
        
        # Add metadata to the schema
        metadata = {
            'date': str(date),
            'total_records': str(total_records),
            'id_range': f"{first_id}-{last_id}",
            'created_at': datetime.datetime.now().isoformat(),
            'created_by': 'StatementExporter',
            'schema_version': '1.0'
        }
        
        # Convert metadata values to bytes as required by pyarrow
        metadata_bytes = {k: str(v).encode('utf-8') for k, v in metadata.items()}
        final_table = final_table.replace_schema_metadata(metadata_bytes)
        
        self._echo(f"Writing final file to {os.path.abspath(f'{output_dir}/sor-global-{date}.parquet')}...")
        
        # Write the final table
        pq.write_table(final_table, os.path.abspath(f"{output_dir}/sor-global-{date}.parquet"), compression='snappy')
        
        self._echo("Generating SHA1 hash...")
        sha1_path, _ = self.generate_sha1(final_file)
        
        # Upload both files to S3
        self._echo("Uploading files to S3...")
        final_upload_success = self.upload_to_s3(final_file, s3_key)
        sha1_upload_success = self.upload_to_s3(sha1_path, f"{s3_key}.sha1")
        
        # If both uploads were successful, delete local files
        if final_upload_success and sha1_upload_success:
            self._echo("Uploads successful, cleaning up local files...")
            try:
                os.remove(final_file)
                os.remove(sha1_path)
                self._echo("Local files cleaned up successfully")
            except Exception as e:
                self._echo(f"Error cleaning up local files: {str(e)}")
        else:
            self._echo("S3 uploads were not fully successful, keeping local files")
        
        # Cleanup temporary chunk files
        self._echo("Cleaning up temporary files...")
        for temp_file in temp_files:
            try:
                os.remove(temp_file)
            except Exception as e:
                self._echo(f"Error removing temporary file {temp_file}: {str(e)}")
        
        total_time = (datetime.datetime.now() - start_time).total_seconds()
        self._echo(f"Export completed: {os.path.abspath(f'{output_dir}/sor-global-{date}.parquet')}")
        self._echo(f"Total records: {total_records}")
        self._echo(f"Total time: {total_time/60:.1f} minutes")
        self._echo(f"Average speed: {total_records/total_time:.1f} records/second")


def parse_date_argument(date_str: Optional[str] = None) -> datetime.date:
    """
    Parse the date argument which can be either:
    - A number of days back (e.g., '7' for 7 days ago)
    - A specific date in YYYY-MM-DD format
    - None, which defaults to yesterday
    
    Returns:
        datetime.date: The parsed date
    """
    if date_str is None:
        # Default to yesterday
        return datetime.date.today() - datetime.timedelta(days=1)
    
    try:
        # Try parsing as number of days back
        days_back = int(date_str)
        return datetime.date.today() - datetime.timedelta(days=days_back)
    except ValueError:
        try:
            # Try parsing as YYYY-MM-DD
            return datetime.datetime.strptime(date_str, '%Y-%m-%d').date()
        except ValueError:
            raise ValueError(
                "Date must be either number of days back (e.g., '7') "
                "or specific date (YYYY-MM-DD)"
            )


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Export statements to parquet format')
    parser.add_argument('-v', '--verbose', action='store_true', help='Show progress output')
    parser.add_argument(
        '-d', '--date', 
        type=str,
        help='Date to export: either days back as number (e.g. 7) or specific date (YYYY-MM-DD), defaults to 1 (yesterday)'
    )
    parser.add_argument(
        '-o', '--output-dir',
        type=str,
        default='storage/app',
        help='Output directory for parquet files (default: storage/app)'
    )
    
    args = parser.parse_args()
    
    try:
        export_date = parse_date_argument(args.date)
        exporter = StatementExporter(verbose=args.verbose)
        exporter.export_day(export_date, args.output_dir)
    except ValueError as e:
        print(f"Error: {str(e)}")
        parser.print_help()
        sys.exit(1)
    except Exception as e:
        print(f"Error during export: {str(e)}")
        sys.exit(1)
