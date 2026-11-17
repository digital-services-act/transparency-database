<?php

namespace Tests\Feature\Services;

use App\Services\CsvRowWriterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CsvRowWriterServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_uses_rfc_4180_quote_escaping_for_problematic_patterns(): void
    {
        $writer = app(CsvRowWriterService::class);

        $patterns = [
            'current backslash-escaped quote pattern' => 'quote \\" value',
            'mixed single and double quotes' => "single ' and double \" quotes",
            'multiple double quotes' => '""""',
        ];

        foreach ($patterns as $description => $value) {
            $csv = $writer->formatRow([$value]);

            $this->assertSame([$value], str_getcsv($csv, ',', '"', ''), $description);
        }

        $this->assertSame(
            '"quote \\"" value"',
            $writer->formatRow(['quote \\" value'])
        );
        $this->assertSame(
            '"single \' and double "" quotes"',
            $writer->formatRow(["single ' and double \" quotes"])
        );
        $this->assertSame(str_repeat('"', 10), $writer->formatRow(['""""']));
    }

    public function test_it_writes_records_with_rfc_4180_crlf_endings(): void
    {
        $writer = app(CsvRowWriterService::class);
        $stream = fopen('php://memory', 'wb+');

        $this->assertNotFalse($stream);
        $writer->writeRow($stream, ['field', 'value']);
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        $this->assertIsString($csv);
        $this->assertSame("field,value\r\n", $csv);
    }

    public function test_format_row_returns_an_empty_string_when_the_memory_stream_cannot_open(): void
    {
        $writer = new class extends CsvRowWriterService
        {
            protected function openMemoryStream(): mixed
            {
                return false;
            }
        };

        $this->assertSame('', $writer->formatRow(['field']));
    }

    public function test_format_row_returns_an_empty_string_when_writing_fails(): void
    {
        $writer = new class extends CsvRowWriterService
        {
            public function writeRow($stream, array $fields): int|false
            {
                return false;
            }
        };

        $this->assertSame('', $writer->formatRow(['field']));
    }

    public function test_format_row_returns_an_empty_string_when_the_memory_stream_cannot_be_read(): void
    {
        $writer = new class extends CsvRowWriterService
        {
            protected function readMemoryStream($stream): string|false
            {
                return false;
            }
        };

        $this->assertSame('', $writer->formatRow(['field']));
    }

    public function test_format_row_sanitizes_stringable_json_and_malformed_utf8_values(): void
    {
        $writer = app(CsvRowWriterService::class);
        $stringable = new class implements \Stringable
        {
            public function __toString(): string
            {
                return "printable\nvalue";
            }
        };
        $throwingStringable = new class implements \Stringable
        {
            public function __toString(): string
            {
                throw new \RuntimeException('Cannot convert to string.');
            }
        };
        $throwingJsonValue = new class implements \JsonSerializable
        {
            public function jsonSerialize(): mixed
            {
                throw new \RuntimeException('Cannot encode value.');
            }
        };

        $csv = $writer->formatRow([
            $stringable,
            $throwingStringable,
            ['key' => 'value'],
            $throwingJsonValue,
            "\xFF",
        ]);

        $this->assertSame(
            ['printable value', '', '{"key":"value"}', '', '?'],
            str_getcsv($csv, ',', '"', '')
        );
    }
}
