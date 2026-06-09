<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Parsedown as ParsedownOriginal;

/**
 * @codeCoverageIgnore
 */
class Parsedown extends ParsedownOriginal
{
    protected function blockTable($Line, ?array $Block = null)
    {
        $Block = parent::blockTable($Line, $Block);

        if (is_null($Block)) {
            return;
        }

        $Block['element']['attributes']['class'] = 'ecl-table-responsive ecl-table ecl-table--zebra';
        $childrenKey = $this->childrenKey($Block['element']);

        if (! isset($Block['element'][$childrenKey])) {
            return $Block;
        }

        foreach ($Block['element'][$childrenKey] as &$elem) {
            if ($elem['name'] === 'thead') {
                $elem['attributes']['class'] = 'ecl-table__head';
                $rowsKey = $this->childrenKey($elem);

                if (! isset($elem[$rowsKey][0])) {
                    continue;
                }

                $elem[$rowsKey][0]['attributes']['class'] = 'ecl-table__row';
                $cellsKey = $this->childrenKey($elem[$rowsKey][0]);

                foreach ($elem[$rowsKey][0][$cellsKey] ?? [] as &$th) {
                    $th['attributes']['class'] = 'ecl-table__header';
                    $th['attributes']['style'] = 'font-size: 11px; text-align: center';
                }
            }

            if ($elem['name'] === 'tbody') {
                $elem['attributes']['class'] = 'ecl-table__body';
                $elem['attributes']['style'] = 'font-size: 10px';
            }
        }

        return $Block;
    }

    protected function blockTableContinue($Line, array $Block)
    {
        $Block = parent::blockTableContinue($Line, $Block);

        if (is_null($Block)) {
            return;
        }

        $childrenKey = $this->childrenKey($Block['element']);
        if (! isset($Block['element'][$childrenKey][1])) {
            return $Block;
        }

        $tbody = &$Block['element'][$childrenKey][1];
        $tbody['attributes']['class'] = 'ecl-table__body';
        $tbody['attributes']['style'] = 'font-size: 10px';

        $rowsKey = $this->childrenKey($tbody);
        $lastRowIndex = array_key_last($tbody[$rowsKey] ?? []);
        if ($lastRowIndex === null) {
            return $Block;
        }

        $row = &$tbody[$rowsKey][$lastRowIndex];
        $row['attributes']['class'] = 'ecl-table__row';
        $cellsKey = $this->childrenKey($row);

        foreach ($row[$cellsKey] ?? [] as &$cell) {
            $cell['attributes']['class'] = 'ecl-table__cell';
        }

        return $Block;
    }

    protected function blockTableComplete(array $Block)
    {
        $Block['element'] = [
            'name'       => 'div',
            'elements'   => [$Block['element']],
            'attributes' => [
                'class' => 'ecl-table-responsive',
                // 'style' => 'width: 110%; font-size: 11px;'
            ],
        ];

        return $Block;
    }

    private function childrenKey(array $element): string
    {
        return array_key_exists('elements', $element) ? 'elements' : 'text';
    }
}
