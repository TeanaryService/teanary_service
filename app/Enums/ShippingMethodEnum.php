<?php

namespace App\Enums;

enum ShippingMethodEnum: string
{
    case SF_INTERNATIONAL = 'sf_international';
    case EMS_INTERNATIONAL = 'ems_international';

    public function label(): string
    {
        return match ($this) {
            self::SF_INTERNATIONAL => __('shipping.method.sf_international'),
            self::EMS_INTERNATIONAL => __('shipping.method.ems_international'),
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    public function apiParams(): array
    {
        return match ($this) {
            self::SF_INTERNATIONAL => [
                'zones' => [
                    1 => ['base_doc' => 112, 'base_item' => 124, 'per_kg' => 54, 'countries' => ['KR']],  // 韩国
                    2 => ['base_doc' => 129, 'base_item' => 143, 'per_kg' => 60, 'countries' => ['SG', 'MY', 'VN', 'TH']],  // 新加坡等
                    3 => ['base_doc' => 177, 'base_item' => 195, 'per_kg' => 65, 'countries' => ['JP']],  // 日本
                    4 => ['base_doc' => 204, 'base_item' => 221, 'per_kg' => 113, 'countries' => ['NZ', 'AU']],  // 澳新
                    5 => ['base_doc' => 209, 'base_item' => 233, 'per_kg' => 95, 'countries' => ['PH', 'BD', 'IN', 'NP']],  // 南亚
                    6 => ['base_doc' => 266, 'base_item' => 285, 'per_kg' => 131, 'countries' => ['US', 'CA', 'MX']],  // 北美
                    7 => ['base_doc' => 276, 'base_item' => 295, 'per_kg' => 117, 'countries' => ['GB', 'FR', 'DE', 'IT', 'ES', 'NL', 'BE', 'SE', 'CH', 'AT', 'DK', 'NO', 'FI', 'IE', 'PT', 'GR']],  // 欧洲主要国家
                    8 => ['base_doc' => 288, 'base_item' => 306, 'per_kg' => 148, 'countries' => ['AE', 'BR', 'CL', 'KE']],  // 其他区域1
                    9 => ['base_doc' => 314, 'base_item' => 334, 'per_kg' => 216, 'countries' => ['HR', 'BG', 'LT', 'SI', 'TZ']],  // 其他区域2
                ],
            ],
            self::EMS_INTERNATIONAL => [
                'zones' => [
                    1 => [
                        'base_doc' => 90,
                        'base_item' => 130,
                        'additional' => 30,
                        'countries' => ['MO', 'TW', 'HK'],  // 一区 - 澳门、台湾、香港
                    ],
                    2 => [
                        'base_doc' => 115,
                        'base_item' => 180,
                        'additional' => 40,
                        'countries' => ['KP', 'KR', 'JP'],  // 二区 - 朝鲜、韩国、日本
                    ],
                    3 => [
                        'base_doc' => 130,
                        'base_item' => 190,
                        'additional' => 45,
                        'countries' => ['PH', 'KH', 'MY', 'MN', 'TH', 'SG', 'ID', 'AM'],  // 三区
                    ],
                    4 => [
                        'base_doc' => 160,
                        'base_item' => 210,
                        'additional' => 55,
                        'countries' => ['AU', 'PG', 'NZ'],  // 四区
                    ],
                    5 => [
                        'base_doc' => 180,
                        'base_item' => 240,
                        'additional' => 75,
                        'countries' => ['US'],  // 五区
                    ],
                    6 => [
                        'base_doc' => 220,
                        'base_item' => 280,
                        'additional' => 75,
                        'countries' => [
                            'IE',
                            'AT',
                            'BE',
                            'DK',
                            'FI',
                            'FR',
                            'CA',
                            'LU',
                            'MT',
                            'NO',
                            'PT',
                            'SE',
                            'CH',
                            'ES',
                            'GR',
                            'IT',
                            'GB',
                        ],  // 六区
                    ],
                    7 => [
                        'base_doc' => 240,
                        'base_item' => 300,
                        'additional' => 80,
                        'countries' => ['PK', 'LA', 'BD', 'NP', 'LK', 'TR', 'IN'],  // 七区
                    ],
                    8 => [
                        'base_doc' => 260,
                        'base_item' => 335,
                        'additional' => 100,
                        'countries' => [
                            'AE',
                            'PA',
                            'BR',
                            'BY',
                            'PL',
                            'RU',
                            'CO',
                            'CU',
                            'VE',
                            'CZ',
                            'SY',
                            'MX',
                            'UA',
                            'HU',
                            'IL',
                            'JO',
                        ],  // 八区
                    ],
                    9 => [
                        'base_doc' => 280,
                        'base_item' => 350,
                        'additional' => 110,
                        'countries' => [
                            'OM',
                            'EG',
                            'ET',
                            'EE',
                            'BH',
                            'BG',
                            'BW',
                            'ZA',
                            'ZW',
                            'KM',
                            'CG',
                            'CD',
                            'KZ',
                            'KG',
                            'GN',
                            'GA',
                            'GH',
                            'QA',
                            'CI',
                            'KW',
                            'LV',
                            'LT',
                            'MG',
                            'MW',
                            'MR',
                            'MU',
                            'NE',
                            'NG',
                            'RS',
                            'SL',
                            'SN',
                            'SD',
                            'TJ',
                            'TZ',
                            'TN',
                            'UG',
                            'UZ',
                            'YE',
                            'ZM',
                            'IR',
                            'TD',
                        ],  // 九区
                    ],
                ],
            ],
        };
    }

    public static function random(): self
    {
        return self::cases()[array_rand(self::cases())];
    }
}
