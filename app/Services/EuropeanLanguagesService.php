<?php

namespace App\Services;

class EuropeanLanguagesService
{
    public const EUROPEAN_LANGUAGE_CODES = [
        'EN',
        'BG',
        'HR',
        'CS',
        'DA',
        'NL',
        'ET',
        'FI',
        'FR',
        'DE',
        'EL',
        'HU',
        'GA',
        'IT',
        'LV',
        'LT',
        'MT',
        'PL',
        'PT',
        'RO',
        'SK',
        'SL',
        'ES',
        'SV',
    ];

    public const ALL_LANGUAGES = [
        'AB' => 'Abkhazian',
        'AA' => 'Afar',
        'AF' => 'Afrikaans',
        'AK' => 'Akan',
        'SQ' => 'Albanian',
        'AM' => 'Amharic',
        'AR' => 'Arabic',
        'AN' => 'Aragonese',
        'HY' => 'Armenian',
        'AS' => 'Assamese',
        'AV' => 'Avaric',
        'AE' => 'Avestan',
        'AY' => 'Aymara',
        'AZ' => 'Azerbaijani',
        'BM' => 'Bambara',
        'BA' => 'Bashkir',
        'EU' => 'Basque',
        'BE' => 'Belarusian',
        'BN' => 'Bengali',
        'BH' => 'Bihari languages',
        'BI' => 'Bislama',
        'BS' => 'Bosnian',
        'BR' => 'Breton',
        'BG' => 'Bulgarian',
        'MY' => 'Burmese',
        'CA' => 'Catalan, Valencian',
        'KM' => 'Central Khmer',
        'CH' => 'Chamorro',
        'CE' => 'Chechen',
        'NY' => 'Chichewa, Chewa, Nyanja',
        'ZH' => 'Chinese',
        'CU' => 'Church Slavonic, Old Bulgarian, Old Church Slavonic',
        'CV' => 'Chuvash',
        'KW' => 'Cornish',
        'CO' => 'Corsican',
        'CR' => 'Cree',
        'HR' => 'Croatian',
        'CS' => 'Czech',
        'DA' => 'Danish',
        'DV' => 'Divehi, Dhivehi, Maldivian',
        'NL' => 'Dutch',
        'DZ' => 'Dzongkha',
        'EN' => 'English',
        'EO' => 'Esperanto',
        'ET' => 'Estonian',
        'EE' => 'Ewe',
        'FO' => 'Faroese',
        'FJ' => 'Fijian',
        'FI' => 'Finnish',
        'FR' => 'French',
        'FF' => 'Fulah',
        'GD' => 'Gaelic, Scottish Gaelic',
        'GL' => 'Galician',
        'LG' => 'Ganda',
        'KA' => 'Georgian',
        'DE' => 'German',
        'KI' => 'Gikuyu, Kikuyu',
        'EL' => 'Greek',
        'KL' => 'Greenlandic, Kalaallisut',
        'GN' => 'Guarani',
        'GU' => 'Gujarati',
        'HT' => 'Haitian, Haitian Creole',
        'HA' => 'Hausa',
        'HE' => 'Hebrew',
        'HZ' => 'Herero',
        'HI' => 'Hindi',
        'HO' => 'Hiri Motu',
        'HU' => 'Hungarian',
        'IS' => 'Icelandic',
        'IO' => 'Ido',
        'IG' => 'Igbo',
        'ID' => 'Indonesian',
        'IA' => 'Interlingua (International Auxiliary Language Association)',
        'IE' => 'Interlingue',
        'IU' => 'Inuktitut',
        'IK' => 'Inupiaq',
        'GA' => 'Irish',
        'IT' => 'Italian',
        'JA' => 'Japanese',
        'JV' => 'Javanese',
        'KN' => 'Kannada',
        'KR' => 'Kanuri',
        'KS' => 'Kashmiri',
        'KK' => 'Kazakh',
        'RW' => 'Kinyarwanda',
        'KV' => 'Komi',
        'KG' => 'Kongo',
        'KO' => 'Korean',
        'KJ' => 'Kwanyama, Kuanyama',
        'KU' => 'Kurdish',
        'KY' => 'Kyrgyz',
        'LO' => 'Lao',
        'LA' => 'Latin',
        'LV' => 'Latvian',
        'LB' => 'Letzeburgesch, Luxembourgish',
        'LI' => 'Limburgish, Limburgan, Limburger',
        'LN' => 'Lingala',
        'LT' => 'Lithuanian',
        'LU' => 'Luba-Katanga',
        'MK' => 'Macedonian',
        'MG' => 'Malagasy',
        'MS' => 'Malay',
        'ML' => 'Malayalam',
        'MT' => 'Maltese',
        'GV' => 'Manx',
        'MI' => 'Maori',
        'MR' => 'Marathi',
        'MH' => 'Marshallese',
        'RO' => 'Romanian',
        'MN' => 'Mongolian',
        'NA' => 'Nauru',
        'NV' => 'Navajo, Navaho',
        'ND' => 'Northern Ndebele',
        'NG' => 'Ndonga',
        'NE' => 'Nepali',
        'SE' => 'Northern Sami',
        'NO' => 'Norwegian',
        'NB' => 'Norwegian Bokmål',
        'NN' => 'Norwegian Nynorsk',
        'II' => 'Nuosu, Sichuan Yi',
        'OC' => 'Occitan (post 1500)',
        'OJ' => 'Ojibwa',
        'OR' => 'Oriya',
        'OM' => 'Oromo',
        'OS' => 'Ossetian, Ossetic',
        'PI' => 'Pali',
        'PA' => 'Panjabi, Punjabi',
        'PS' => 'Pashto, Pushto',
        'FA' => 'Persian',
        'PL' => 'Polish',
        'PT' => 'Portuguese',
        'QU' => 'Quechua',
        'RM' => 'Romansh',
        'RN' => 'Rundi',
        'RU' => 'Russian',
        'SM' => 'Samoan',
        'SG' => 'Sango',
        'SA' => 'Sanskrit',
        'SC' => 'Sardinian',
        'SR' => 'Serbian',
        'SN' => 'Shona',
        'SD' => 'Sindhi',
        'SI' => 'Sinhala, Sinhalese',
        'SK' => 'Slovak',
        'SL' => 'Slovenian',
        'SO' => 'Somali',
        'ST' => 'Sotho, Southern',
        'NR' => 'South Ndebele',
        'ES' => 'Spanish',
        'SU' => 'Sundanese',
        'SW' => 'Swahili',
        'SS' => 'Swati',
        'SV' => 'Swedish',
        'TL' => 'Tagalog',
        'TY' => 'Tahitian',
        'TG' => 'Tajik',
        'TA' => 'Tamil',
        'TT' => 'Tatar',
        'TE' => 'Telugu',
        'TH' => 'Thai',
        'BO' => 'Tibetan',
        'TI' => 'Tigrinya',
        'TO' => 'Tonga (Tonga Islands)',
        'TS' => 'Tsonga',
        'TN' => 'Tswana',
        'TR' => 'Turkish',
        'TK' => 'Turkmen',
        'TW' => 'Twi',
        'UG' => 'Uighur, Uyghur',
        'UK' => 'Ukrainian',
        'UR' => 'Urdu',
        'UZ' => 'Uzbek',
        'VE' => 'Venda',
        'VI' => 'Vietnamese',
        'VO' => 'Volapük',
        'WA' => 'Walloon',
        'CY' => 'Welsh',
        'FY' => 'Western Frisian',
        'WO' => 'Wolof',
        'XH' => 'Xhosa',
        'YI' => 'Yiddish',
        'YO' => 'Yoruba',
        'ZA' => 'Zhuang, Chuang',
        'ZU' => 'Zulu'
    ];

    public function getName(string $iso) : string|bool
    {
        $iso = strtoupper($iso);
        return self::ALL_LANGUAGES[$iso] ?? false;
    }

    public function getEuropeanLanguages()
    {
        $out = [];
        foreach (self::EUROPEAN_LANGUAGE_CODES as $iso) {
            $out[$iso] = $this->getName($iso);
        }

        return $out;
    }

    public function getNonEuropeanLanguages() : array
    {
        $out = [];
        foreach (self::ALL_LANGUAGES as $iso => $name)
        {
            if (!in_array($iso, self::EUROPEAN_LANGUAGE_CODES)) {
                $out[$iso] = $name;
            }
        }

        return $out;
    }

    public function getAllLanguages(bool $euro_centric = false, bool $with_breakers = false) : array
    {
        if ($euro_centric && $with_breakers)
        {
            return  //[' ' => ' '] +
                    ['  ' => '-- European Languages --'] +
                    //['   ' => ' '] +
                    $this->getEuropeanLanguages() +
                    ['    ' => ' ' ] +
                    ['     ' => '-- Other Languages --'] +
                    //['      ' => ' ' ] +
                    $this->getNonEuropeanLanguages();
        }

        if ($euro_centric)
        {
            return $this->getEuropeanLanguages() + $this->getNonEuropeanLanguages();
        }

        return self::ALL_LANGUAGES;
    }
}