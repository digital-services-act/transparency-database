<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Parsedown as ParsedownOriginal;

class Parsedown extends ParsedownOriginal {

    protected function blockTable($Line, ?array $Block = null)
    {
        $Block = parent::blockTable($Line, $Block);

        if(is_null($Block)){ return; }

        $Block['element']['attributes']['class'] = 'ecl-table-responsive ecl-table ecl-table--zebra';

        foreach ($Block['element']['text'] as &$elem) {
            if ($elem['name'] === 'thead') {
                $elem['attributes']['class'] = 'ecl-table__head';
                $elem['text'][0]['attributes']['class'] = 'ecl-table__row';

                foreach ($elem['text'][0]['text'] as &$th) {
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
        if (isset($Block['interrupted']))
        {
            return;
        }

        if ($Line['text'][0] === '|' or strpos($Line['text'], '|'))
        {
            $Elements = array();

            $row = $Line['text'];

            $row = trim($row);
            $row = trim($row, '|');

            preg_match_all('/(?:(\\\\[|])|[^|`]|`[^`]+`|`)+/', $row, $matches);

            foreach ($matches[0] as $index => $cell)
            {
                $cell = trim($cell);

                $Element = array(
                    'name' => 'td',
                    'handler' => 'line',
                    'text' => $cell,
                    'attributes' => [
                        'class' => 'ecl-table__cell'
                    ]
                );

                if (isset($Block['alignments'][$index]))
                {
                    $Element['attributes'] = array(
                        'style' => 'text-align: '.$Block['alignments'][$index].';',
                    );
                }

                $Elements []= $Element;
            }

            $Element = array(
                'name' => 'tr',
                'handler' => 'elements',
                'text' => $Elements,
                'attributes' => [
                    'class' => 'ecl-table__row'
                ]
            );

            $Block['element']['text'][1]['text'] []= $Element;

            return $Block;
        }
    }

    protected function blockTableComplete(array $Block)
    {
        $Block['element'] = [
            'name'       => 'div',
            'handler'    => 'elements',
            'text'       => [$Block['element']],
            'attributes' => [
                'class' => 'ecl-table-responsive',
                // 'style' => 'width: 110%; font-size: 11px;'
            ],
        ];

        return $Block;
    }
}
