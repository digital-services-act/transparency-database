<?php

namespace App\Services;

class CsvRowWriterService
{
    /**
     * Write one CSV record using RFC 4180 quote escaping and a CRLF ending.
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
        $fields = array_map($this->sanitizeField(...), $fields);

        return $this->formatPreSanitizedRow($fields);
    }

    /**
     * Convert fields already sanitized by the source query into one CSV record.
     *
     * @param  array<int, mixed>  $fields
     */
    public function formatPreSanitizedRow(array $fields): string
    {
        $stream = $this->openMemoryStream();
        if ($stream === false) {
            return '';
        }

        try {
            if ($this->writeRow($stream, $fields) === false) {
                return '';
            }

            rewind($stream);
            $csv_line = $this->readMemoryStream($stream);
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

    protected function openMemoryStream(): mixed
    {
        return fopen('php://memory', 'wb+');
    }

    /** @param resource $stream */
    protected function readMemoryStream($stream): string|false
    {
        return stream_get_contents($stream);
    }

    private function sanitizeField(mixed $field): string
    {
        if ($field === null) {
            $value = '';
        } elseif (is_scalar($field)) {
            $value = (string) $field;
        } elseif ($field instanceof \Stringable) {
            try {
                $value = (string) $field;
            } catch (\Throwable) {
                $value = '';
            }
        } else {
            try {
                $encoded = json_encode($field, JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR);
            } catch (\Throwable) {
                $encoded = false;
            }

            if ($encoded === false) {
                $value = '';
            } else {
                $value = $encoded;
            }
        }

        // Keep line breaks out of field contents while preserving valid UTF-8.
        $value = str_replace(["\r\n", "\r", "\n"], ' ', $value);

        if (preg_match('//u', $value) === 1) {
            return preg_replace('/\p{Cc}/u', '?', $value) ?? '';
        }

        // Malformed UTF-8 is replaced bytewise in the fallback path.
        return preg_replace('/[\x00-\x1F\x7F-\xFF]/', '?', $value) ?? '';
    }
}
