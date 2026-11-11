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
}
