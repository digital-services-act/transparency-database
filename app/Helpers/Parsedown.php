<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Parsedown as ParsedownOriginal;

/**
 * @codeCoverageIgnore
 */
class Parsedown extends ParsedownOriginal {

    protected function blockTable($Line, ?array $Block = null)
    {
        $Block = parent::blockTable($Line, $Block);

        if (is_null($Block)) {
            return;
        }

        $Block['element']['attributes']['class'] = 'ecl-table-responsive ecl-table ecl-table--zebra';

        foreach ($Block['element']['elements'] ?? [] as &$elem) {
            if ($elem['name'] === 'thead') {
                $elem['attributes']['class'] = 'ecl-table__head';
                $elem['elements'][0]['attributes']['class'] = 'ecl-table__row';

                foreach ($elem['elements'][0]['elements'] ?? [] as &$th) {
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
        if (isset($Block['interrupted'])) {
            return;
        }

        if (count($Block['alignments']) === 1 || $Line['text'][0] === '|' || strpos($Line['text'], '|')) {
            $Elements = array();

            $row = $Line['text'];

            $row = trim($row);
            $row = trim($row, '|');

            preg_match_all('/(?:(\\\\[|])|[^|`]|`[^`]++`|`)++/', $row, $matches);

            $cells = array_slice($matches[0], 0, count($Block['alignments']));

            foreach ($cells as $index => $cell) {
                $cell = trim($cell);

                $Element = array(
                    'name' => 'td',
                    'handler' => array(
                        'function' => 'lineElements',
                        'argument' => $cell,
                        'destination' => 'elements',
                    ),
                    'attributes' => [
                        'class' => 'ecl-table__cell'
                    ]
                );

                if (isset($Block['alignments'][$index])) {
                    $Element['attributes']['style'] = 'text-align: ' . $Block['alignments'][$index] . ';';
                }

                $Elements []= $Element;
            }

            $Element = array(
                'name' => 'tr',
                'elements' => $Elements,
                'attributes' => [
                    'class' => 'ecl-table__row'
                ]
            );

            $Block['element']['elements'][1]['elements'] []= $Element;

            return $Block;
        }
    }

    protected function blockTableComplete(array $Block)
    {
        $Block['element'] = [
            'name' => 'div',
            'elements' => [$Block['element']],
            'attributes' => [
                'class' => 'ecl-table-responsive',
            ],
        ];

        return $Block;
    }
}
