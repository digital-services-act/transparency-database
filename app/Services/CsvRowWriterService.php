<?php

namespace App\Services;

class CsvRowWriterService
{
    /**
     * Write one CSV record using RFC 4180 quote escaping and record endings.
     *
     * @param  resource  $stream
     * @param  array<int, mixed>  $fields
     */
    public function writeRow($stream, array $fields): int|false
    {
        return fputcsv($stream, $fields, ',', '"', '', "\r\n");
    }

    /**
     * Convert a row to a CSV record without its record-ending CRLF.
     *
     * @param  array<int, mixed>  $fields
     */
    public function formatRow(array $fields): string
    {
        $stream = fopen('php://memory', 'wb+');
        if ($stream === false) {
            return '';
        }

        try {
            if ($this->writeRow($stream, $fields) === false) {
                return '';
            }

            rewind($stream);
            $csv_line = stream_get_contents($stream);
            if ($csv_line === false) {
                return '';
            }

            return str_ends_with($csv_line, "\r\n")
                ? substr($csv_line, 0, -2)
                : $csv_line;
        } finally {
            fclose($stream);
        }
    }
}
