<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CountriesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('countries')->delete();
        
        \DB::table('countries')->insert(array (
            0 => 
            array (
                'id' => 1,
                'iso_code_2' => 'AF',
                'iso_code_3' => 'AFG',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            1 => 
            array (
                'id' => 2,
                'iso_code_2' => 'AL',
                'iso_code_3' => 'ALB',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            2 => 
            array (
                'id' => 3,
                'iso_code_2' => 'DZ',
                'iso_code_3' => 'DZA',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            3 => 
            array (
                'id' => 4,
                'iso_code_2' => 'AS',
                'iso_code_3' => 'ASM',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            4 => 
            array (
                'id' => 5,
                'iso_code_2' => 'AD',
                'iso_code_3' => 'AND',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            5 => 
            array (
                'id' => 6,
                'iso_code_2' => 'AO',
                'iso_code_3' => 'AGO',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            6 => 
            array (
                'id' => 7,
                'iso_code_2' => 'AI',
                'iso_code_3' => 'AIA',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            7 => 
            array (
                'id' => 8,
                'iso_code_2' => 'AQ',
                'iso_code_3' => 'ATA',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            8 => 
            array (
                'id' => 9,
                'iso_code_2' => 'AG',
                'iso_code_3' => 'ATG',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            9 => 
            array (
                'id' => 10,
                'iso_code_2' => 'AR',
                'iso_code_3' => 'ARG',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            10 => 
            array (
                'id' => 11,
                'iso_code_2' => 'AM',
                'iso_code_3' => 'ARM',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            11 => 
            array (
                'id' => 12,
                'iso_code_2' => 'AW',
                'iso_code_3' => 'ABW',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            12 => 
            array (
                'id' => 13,
                'iso_code_2' => 'AU',
                'iso_code_3' => 'AUS',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            13 => 
            array (
                'id' => 14,
                'iso_code_2' => 'AT',
                'iso_code_3' => 'AUT',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            14 => 
            array (
                'id' => 15,
                'iso_code_2' => 'AZ',
                'iso_code_3' => 'AZE',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            15 => 
            array (
                'id' => 16,
                'iso_code_2' => 'BS',
                'iso_code_3' => 'BHS',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            16 => 
            array (
                'id' => 17,
                'iso_code_2' => 'BH',
                'iso_code_3' => 'BHR',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            17 => 
            array (
                'id' => 18,
                'iso_code_2' => 'BD',
                'iso_code_3' => 'BGD',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            18 => 
            array (
                'id' => 19,
                'iso_code_2' => 'BB',
                'iso_code_3' => 'BRB',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            19 => 
            array (
                'id' => 20,
                'iso_code_2' => 'BY',
                'iso_code_3' => 'BLR',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            20 => 
            array (
                'id' => 21,
                'iso_code_2' => 'BE',
                'iso_code_3' => 'BEL',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            21 => 
            array (
                'id' => 22,
                'iso_code_2' => 'BZ',
                'iso_code_3' => 'BLZ',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            22 => 
            array (
                'id' => 23,
                'iso_code_2' => 'BJ',
                'iso_code_3' => 'BEN',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            23 => 
            array (
                'id' => 24,
                'iso_code_2' => 'BM',
                'iso_code_3' => 'BMU',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            24 => 
            array (
                'id' => 25,
                'iso_code_2' => 'BT',
                'iso_code_3' => 'BTN',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            25 => 
            array (
                'id' => 26,
                'iso_code_2' => 'BO',
                'iso_code_3' => 'BOL',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            26 => 
            array (
                'id' => 27,
                'iso_code_2' => 'BA',
                'iso_code_3' => 'BIH',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            27 => 
            array (
                'id' => 28,
                'iso_code_2' => 'BW',
                'iso_code_3' => 'BWA',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            28 => 
            array (
                'id' => 29,
                'iso_code_2' => 'BV',
                'iso_code_3' => 'BVT',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            29 => 
            array (
                'id' => 30,
                'iso_code_2' => 'BR',
                'iso_code_3' => 'BRA',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            30 => 
            array (
                'id' => 31,
                'iso_code_2' => 'IO',
                'iso_code_3' => 'IOT',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            31 => 
            array (
                'id' => 32,
                'iso_code_2' => 'BN',
                'iso_code_3' => 'BRN',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            32 => 
            array (
                'id' => 33,
                'iso_code_2' => 'BG',
                'iso_code_3' => 'BGR',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            33 => 
            array (
                'id' => 34,
                'iso_code_2' => 'BF',
                'iso_code_3' => 'BFA',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            34 => 
            array (
                'id' => 35,
                'iso_code_2' => 'BI',
                'iso_code_3' => 'BDI',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            35 => 
            array (
                'id' => 36,
                'iso_code_2' => 'KH',
                'iso_code_3' => 'KHM',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            36 => 
            array (
                'id' => 37,
                'iso_code_2' => 'CM',
                'iso_code_3' => 'CMR',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            37 => 
            array (
                'id' => 38,
                'iso_code_2' => 'CA',
                'iso_code_3' => 'CAN',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            38 => 
            array (
                'id' => 39,
                'iso_code_2' => 'CV',
                'iso_code_3' => 'CPV',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            39 => 
            array (
                'id' => 40,
                'iso_code_2' => 'KY',
                'iso_code_3' => 'CYM',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            40 => 
            array (
                'id' => 41,
                'iso_code_2' => 'CF',
                'iso_code_3' => 'CAF',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            41 => 
            array (
                'id' => 42,
                'iso_code_2' => 'TD',
                'iso_code_3' => 'TCD',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            42 => 
            array (
                'id' => 43,
                'iso_code_2' => 'CL',
                'iso_code_3' => 'CHL',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            43 => 
            array (
                'id' => 44,
                'iso_code_2' => 'CN',
                'iso_code_3' => 'CHN',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            44 => 
            array (
                'id' => 45,
                'iso_code_2' => 'CX',
                'iso_code_3' => 'CXR',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            45 => 
            array (
                'id' => 46,
                'iso_code_2' => 'CC',
                'iso_code_3' => 'CCK',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            46 => 
            array (
                'id' => 47,
                'iso_code_2' => 'CO',
                'iso_code_3' => 'COL',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            47 => 
            array (
                'id' => 48,
                'iso_code_2' => 'KM',
                'iso_code_3' => 'COM',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            48 => 
            array (
                'id' => 49,
                'iso_code_2' => 'CG',
                'iso_code_3' => 'COG',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            49 => 
            array (
                'id' => 50,
                'iso_code_2' => 'CK',
                'iso_code_3' => 'COK',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            50 => 
            array (
                'id' => 51,
                'iso_code_2' => 'CR',
                'iso_code_3' => 'CRI',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            51 => 
            array (
                'id' => 52,
                'iso_code_2' => 'CI',
                'iso_code_3' => 'CIV',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            52 => 
            array (
                'id' => 53,
                'iso_code_2' => 'HR',
                'iso_code_3' => 'HRV',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            53 => 
            array (
                'id' => 54,
                'iso_code_2' => 'CU',
                'iso_code_3' => 'CUB',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            54 => 
            array (
                'id' => 55,
                'iso_code_2' => 'CY',
                'iso_code_3' => 'CYP',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            55 => 
            array (
                'id' => 56,
                'iso_code_2' => 'CZ',
                'iso_code_3' => 'CZE',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            56 => 
            array (
                'id' => 57,
                'iso_code_2' => 'DK',
                'iso_code_3' => 'DNK',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            57 => 
            array (
                'id' => 58,
                'iso_code_2' => 'DJ',
                'iso_code_3' => 'DJI',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            58 => 
            array (
                'id' => 59,
                'iso_code_2' => 'DM',
                'iso_code_3' => 'DMA',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            59 => 
            array (
                'id' => 60,
                'iso_code_2' => 'DO',
                'iso_code_3' => 'DOM',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            60 => 
            array (
                'id' => 61,
                'iso_code_2' => 'TL',
                'iso_code_3' => 'TLS',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            61 => 
            array (
                'id' => 62,
                'iso_code_2' => 'EC',
                'iso_code_3' => 'ECU',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            62 => 
            array (
                'id' => 63,
                'iso_code_2' => 'EG',
                'iso_code_3' => 'EGY',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            63 => 
            array (
                'id' => 64,
                'iso_code_2' => 'SV',
                'iso_code_3' => 'SLV',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            64 => 
            array (
                'id' => 65,
                'iso_code_2' => 'GQ',
                'iso_code_3' => 'GNQ',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            65 => 
            array (
                'id' => 66,
                'iso_code_2' => 'ER',
                'iso_code_3' => 'ERI',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            66 => 
            array (
                'id' => 67,
                'iso_code_2' => 'EE',
                'iso_code_3' => 'EST',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            67 => 
            array (
                'id' => 68,
                'iso_code_2' => 'ET',
                'iso_code_3' => 'ETH',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            68 => 
            array (
                'id' => 69,
                'iso_code_2' => 'FK',
                'iso_code_3' => 'FLK',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            69 => 
            array (
                'id' => 70,
                'iso_code_2' => 'FO',
                'iso_code_3' => 'FRO',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            70 => 
            array (
                'id' => 71,
                'iso_code_2' => 'FJ',
                'iso_code_3' => 'FJI',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            71 => 
            array (
                'id' => 72,
                'iso_code_2' => 'FI',
                'iso_code_3' => 'FIN',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            72 => 
            array (
                'id' => 73,
                'iso_code_2' => 'FR',
                'iso_code_3' => 'FRA',
                'postcode_required' => 1,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            73 => 
            array (
                'id' => 74,
                'iso_code_2' => 'GF',
                'iso_code_3' => 'GUF',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            74 => 
            array (
                'id' => 75,
                'iso_code_2' => 'PF',
                'iso_code_3' => 'PYF',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            75 => 
            array (
                'id' => 76,
                'iso_code_2' => 'TF',
                'iso_code_3' => 'ATF',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            76 => 
            array (
                'id' => 77,
                'iso_code_2' => 'GA',
                'iso_code_3' => 'GAB',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            77 => 
            array (
                'id' => 78,
                'iso_code_2' => 'GM',
                'iso_code_3' => 'GMB',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            78 => 
            array (
                'id' => 79,
                'iso_code_2' => 'GE',
                'iso_code_3' => 'GEO',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            79 => 
            array (
                'id' => 80,
                'iso_code_2' => 'DE',
                'iso_code_3' => 'DEU',
                'postcode_required' => 1,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            80 => 
            array (
                'id' => 81,
                'iso_code_2' => 'GH',
                'iso_code_3' => 'GHA',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            81 => 
            array (
                'id' => 82,
                'iso_code_2' => 'GI',
                'iso_code_3' => 'GIB',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            82 => 
            array (
                'id' => 83,
                'iso_code_2' => 'GR',
                'iso_code_3' => 'GRC',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            83 => 
            array (
                'id' => 84,
                'iso_code_2' => 'GL',
                'iso_code_3' => 'GRL',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            84 => 
            array (
                'id' => 85,
                'iso_code_2' => 'GD',
                'iso_code_3' => 'GRD',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            85 => 
            array (
                'id' => 86,
                'iso_code_2' => 'GP',
                'iso_code_3' => 'GLP',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            86 => 
            array (
                'id' => 87,
                'iso_code_2' => 'GU',
                'iso_code_3' => 'GUM',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            87 => 
            array (
                'id' => 88,
                'iso_code_2' => 'GT',
                'iso_code_3' => 'GTM',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            88 => 
            array (
                'id' => 89,
                'iso_code_2' => 'GN',
                'iso_code_3' => 'GIN',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            89 => 
            array (
                'id' => 90,
                'iso_code_2' => 'GW',
                'iso_code_3' => 'GNB',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            90 => 
            array (
                'id' => 91,
                'iso_code_2' => 'GY',
                'iso_code_3' => 'GUY',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            91 => 
            array (
                'id' => 92,
                'iso_code_2' => 'HT',
                'iso_code_3' => 'HTI',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            92 => 
            array (
                'id' => 93,
                'iso_code_2' => 'HM',
                'iso_code_3' => 'HMD',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            93 => 
            array (
                'id' => 94,
                'iso_code_2' => 'HN',
                'iso_code_3' => 'HND',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            94 => 
            array (
                'id' => 95,
                'iso_code_2' => 'HK',
                'iso_code_3' => 'HKG',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            95 => 
            array (
                'id' => 96,
                'iso_code_2' => 'HU',
                'iso_code_3' => 'HUN',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            96 => 
            array (
                'id' => 97,
                'iso_code_2' => 'IS',
                'iso_code_3' => 'ISL',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            97 => 
            array (
                'id' => 98,
                'iso_code_2' => 'IN',
                'iso_code_3' => 'IND',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            98 => 
            array (
                'id' => 99,
                'iso_code_2' => 'ID',
                'iso_code_3' => 'IDN',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            99 => 
            array (
                'id' => 100,
                'iso_code_2' => 'IR',
                'iso_code_3' => 'IRN',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            100 => 
            array (
                'id' => 101,
                'iso_code_2' => 'IQ',
                'iso_code_3' => 'IRQ',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            101 => 
            array (
                'id' => 102,
                'iso_code_2' => 'IE',
                'iso_code_3' => 'IRL',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            102 => 
            array (
                'id' => 103,
                'iso_code_2' => 'IL',
                'iso_code_3' => 'ISR',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            103 => 
            array (
                'id' => 104,
                'iso_code_2' => 'IT',
                'iso_code_3' => 'ITA',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            104 => 
            array (
                'id' => 105,
                'iso_code_2' => 'JM',
                'iso_code_3' => 'JAM',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            105 => 
            array (
                'id' => 106,
                'iso_code_2' => 'JP',
                'iso_code_3' => 'JPN',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            106 => 
            array (
                'id' => 107,
                'iso_code_2' => 'JO',
                'iso_code_3' => 'JOR',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            107 => 
            array (
                'id' => 108,
                'iso_code_2' => 'KZ',
                'iso_code_3' => 'KAZ',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            108 => 
            array (
                'id' => 109,
                'iso_code_2' => 'KE',
                'iso_code_3' => 'KEN',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            109 => 
            array (
                'id' => 110,
                'iso_code_2' => 'KI',
                'iso_code_3' => 'KIR',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            110 => 
            array (
                'id' => 111,
                'iso_code_2' => 'KP',
                'iso_code_3' => 'PRK',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            111 => 
            array (
                'id' => 112,
                'iso_code_2' => 'KR',
                'iso_code_3' => 'KOR',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            112 => 
            array (
                'id' => 113,
                'iso_code_2' => 'KW',
                'iso_code_3' => 'KWT',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            113 => 
            array (
                'id' => 114,
                'iso_code_2' => 'KG',
                'iso_code_3' => 'KGZ',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            114 => 
            array (
                'id' => 115,
                'iso_code_2' => 'LA',
                'iso_code_3' => 'LAO',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            115 => 
            array (
                'id' => 116,
                'iso_code_2' => 'LV',
                'iso_code_3' => 'LVA',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            116 => 
            array (
                'id' => 117,
                'iso_code_2' => 'LB',
                'iso_code_3' => 'LBN',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            117 => 
            array (
                'id' => 118,
                'iso_code_2' => 'LS',
                'iso_code_3' => 'LSO',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            118 => 
            array (
                'id' => 119,
                'iso_code_2' => 'LR',
                'iso_code_3' => 'LBR',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            119 => 
            array (
                'id' => 120,
                'iso_code_2' => 'LY',
                'iso_code_3' => 'LBY',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            120 => 
            array (
                'id' => 121,
                'iso_code_2' => 'LI',
                'iso_code_3' => 'LIE',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            121 => 
            array (
                'id' => 122,
                'iso_code_2' => 'LT',
                'iso_code_3' => 'LTU',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            122 => 
            array (
                'id' => 123,
                'iso_code_2' => 'LU',
                'iso_code_3' => 'LUX',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            123 => 
            array (
                'id' => 124,
                'iso_code_2' => 'MO',
                'iso_code_3' => 'MAC',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            124 => 
            array (
                'id' => 125,
                'iso_code_2' => 'MK',
                'iso_code_3' => 'MKD',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            125 => 
            array (
                'id' => 126,
                'iso_code_2' => 'MG',
                'iso_code_3' => 'MDG',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            126 => 
            array (
                'id' => 127,
                'iso_code_2' => 'MW',
                'iso_code_3' => 'MWI',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:57',
                'updated_at' => '2025-07-16 18:15:57',
            ),
            127 => 
            array (
                'id' => 128,
                'iso_code_2' => 'MY',
                'iso_code_3' => 'MYS',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            128 => 
            array (
                'id' => 129,
                'iso_code_2' => 'MV',
                'iso_code_3' => 'MDV',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            129 => 
            array (
                'id' => 130,
                'iso_code_2' => 'ML',
                'iso_code_3' => 'MLI',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            130 => 
            array (
                'id' => 131,
                'iso_code_2' => 'MT',
                'iso_code_3' => 'MLT',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            131 => 
            array (
                'id' => 132,
                'iso_code_2' => 'MH',
                'iso_code_3' => 'MHL',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            132 => 
            array (
                'id' => 133,
                'iso_code_2' => 'MQ',
                'iso_code_3' => 'MTQ',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            133 => 
            array (
                'id' => 134,
                'iso_code_2' => 'MR',
                'iso_code_3' => 'MRT',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            134 => 
            array (
                'id' => 135,
                'iso_code_2' => 'MU',
                'iso_code_3' => 'MUS',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            135 => 
            array (
                'id' => 136,
                'iso_code_2' => 'YT',
                'iso_code_3' => 'MYT',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            136 => 
            array (
                'id' => 137,
                'iso_code_2' => 'MX',
                'iso_code_3' => 'MEX',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            137 => 
            array (
                'id' => 138,
                'iso_code_2' => 'FM',
                'iso_code_3' => 'FSM',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            138 => 
            array (
                'id' => 139,
                'iso_code_2' => 'MD',
                'iso_code_3' => 'MDA',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            139 => 
            array (
                'id' => 140,
                'iso_code_2' => 'MC',
                'iso_code_3' => 'MCO',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            140 => 
            array (
                'id' => 141,
                'iso_code_2' => 'MN',
                'iso_code_3' => 'MNG',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            141 => 
            array (
                'id' => 142,
                'iso_code_2' => 'MS',
                'iso_code_3' => 'MSR',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            142 => 
            array (
                'id' => 143,
                'iso_code_2' => 'MA',
                'iso_code_3' => 'MAR',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            143 => 
            array (
                'id' => 144,
                'iso_code_2' => 'MZ',
                'iso_code_3' => 'MOZ',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            144 => 
            array (
                'id' => 145,
                'iso_code_2' => 'MM',
                'iso_code_3' => 'MMR',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            145 => 
            array (
                'id' => 146,
                'iso_code_2' => 'NA',
                'iso_code_3' => 'NAM',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            146 => 
            array (
                'id' => 147,
                'iso_code_2' => 'NR',
                'iso_code_3' => 'NRU',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            147 => 
            array (
                'id' => 148,
                'iso_code_2' => 'NP',
                'iso_code_3' => 'NPL',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            148 => 
            array (
                'id' => 149,
                'iso_code_2' => 'NL',
                'iso_code_3' => 'NLD',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            149 => 
            array (
                'id' => 150,
                'iso_code_2' => 'AN',
                'iso_code_3' => 'ANT',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            150 => 
            array (
                'id' => 151,
                'iso_code_2' => 'NC',
                'iso_code_3' => 'NCL',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            151 => 
            array (
                'id' => 152,
                'iso_code_2' => 'NZ',
                'iso_code_3' => 'NZL',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            152 => 
            array (
                'id' => 153,
                'iso_code_2' => 'NI',
                'iso_code_3' => 'NIC',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            153 => 
            array (
                'id' => 154,
                'iso_code_2' => 'NE',
                'iso_code_3' => 'NER',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            154 => 
            array (
                'id' => 155,
                'iso_code_2' => 'NG',
                'iso_code_3' => 'NGA',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            155 => 
            array (
                'id' => 156,
                'iso_code_2' => 'NU',
                'iso_code_3' => 'NIU',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            156 => 
            array (
                'id' => 157,
                'iso_code_2' => 'NF',
                'iso_code_3' => 'NFK',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            157 => 
            array (
                'id' => 158,
                'iso_code_2' => 'MP',
                'iso_code_3' => 'MNP',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            158 => 
            array (
                'id' => 159,
                'iso_code_2' => 'NO',
                'iso_code_3' => 'NOR',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            159 => 
            array (
                'id' => 160,
                'iso_code_2' => 'OM',
                'iso_code_3' => 'OMN',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            160 => 
            array (
                'id' => 161,
                'iso_code_2' => 'PK',
                'iso_code_3' => 'PAK',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            161 => 
            array (
                'id' => 162,
                'iso_code_2' => 'PW',
                'iso_code_3' => 'PLW',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            162 => 
            array (
                'id' => 163,
                'iso_code_2' => 'PA',
                'iso_code_3' => 'PAN',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            163 => 
            array (
                'id' => 164,
                'iso_code_2' => 'PG',
                'iso_code_3' => 'PNG',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            164 => 
            array (
                'id' => 165,
                'iso_code_2' => 'PY',
                'iso_code_3' => 'PRY',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            165 => 
            array (
                'id' => 166,
                'iso_code_2' => 'PE',
                'iso_code_3' => 'PER',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            166 => 
            array (
                'id' => 167,
                'iso_code_2' => 'PH',
                'iso_code_3' => 'PHL',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            167 => 
            array (
                'id' => 168,
                'iso_code_2' => 'PN',
                'iso_code_3' => 'PCN',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            168 => 
            array (
                'id' => 169,
                'iso_code_2' => 'PL',
                'iso_code_3' => 'POL',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            169 => 
            array (
                'id' => 170,
                'iso_code_2' => 'PT',
                'iso_code_3' => 'PRT',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            170 => 
            array (
                'id' => 171,
                'iso_code_2' => 'PR',
                'iso_code_3' => 'PRI',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            171 => 
            array (
                'id' => 172,
                'iso_code_2' => 'QA',
                'iso_code_3' => 'QAT',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            172 => 
            array (
                'id' => 173,
                'iso_code_2' => 'RE',
                'iso_code_3' => 'REU',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            173 => 
            array (
                'id' => 174,
                'iso_code_2' => 'RO',
                'iso_code_3' => 'ROM',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            174 => 
            array (
                'id' => 175,
                'iso_code_2' => 'RU',
                'iso_code_3' => 'RUS',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            175 => 
            array (
                'id' => 176,
                'iso_code_2' => 'RW',
                'iso_code_3' => 'RWA',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            176 => 
            array (
                'id' => 177,
                'iso_code_2' => 'KN',
                'iso_code_3' => 'KNA',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            177 => 
            array (
                'id' => 178,
                'iso_code_2' => 'LC',
                'iso_code_3' => 'LCA',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            178 => 
            array (
                'id' => 179,
                'iso_code_2' => 'VC',
                'iso_code_3' => 'VCT',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            179 => 
            array (
                'id' => 180,
                'iso_code_2' => 'WS',
                'iso_code_3' => 'WSM',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            180 => 
            array (
                'id' => 181,
                'iso_code_2' => 'SM',
                'iso_code_3' => 'SMR',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            181 => 
            array (
                'id' => 182,
                'iso_code_2' => 'ST',
                'iso_code_3' => 'STP',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            182 => 
            array (
                'id' => 183,
                'iso_code_2' => 'SA',
                'iso_code_3' => 'SAU',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            183 => 
            array (
                'id' => 184,
                'iso_code_2' => 'SN',
                'iso_code_3' => 'SEN',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            184 => 
            array (
                'id' => 185,
                'iso_code_2' => 'SC',
                'iso_code_3' => 'SYC',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            185 => 
            array (
                'id' => 186,
                'iso_code_2' => 'SL',
                'iso_code_3' => 'SLE',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            186 => 
            array (
                'id' => 187,
                'iso_code_2' => 'SG',
                'iso_code_3' => 'SGP',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            187 => 
            array (
                'id' => 188,
                'iso_code_2' => 'SK',
                'iso_code_3' => 'SVK',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            188 => 
            array (
                'id' => 189,
                'iso_code_2' => 'SI',
                'iso_code_3' => 'SVN',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            189 => 
            array (
                'id' => 190,
                'iso_code_2' => 'SB',
                'iso_code_3' => 'SLB',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            190 => 
            array (
                'id' => 191,
                'iso_code_2' => 'SO',
                'iso_code_3' => 'SOM',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            191 => 
            array (
                'id' => 192,
                'iso_code_2' => 'ZA',
                'iso_code_3' => 'ZAF',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            192 => 
            array (
                'id' => 193,
                'iso_code_2' => 'GS',
                'iso_code_3' => 'SGS',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            193 => 
            array (
                'id' => 194,
                'iso_code_2' => 'ES',
                'iso_code_3' => 'ESP',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            194 => 
            array (
                'id' => 195,
                'iso_code_2' => 'LK',
                'iso_code_3' => 'LKA',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            195 => 
            array (
                'id' => 196,
                'iso_code_2' => 'SH',
                'iso_code_3' => 'SHN',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            196 => 
            array (
                'id' => 197,
                'iso_code_2' => 'PM',
                'iso_code_3' => 'SPM',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            197 => 
            array (
                'id' => 198,
                'iso_code_2' => 'SD',
                'iso_code_3' => 'SDN',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            198 => 
            array (
                'id' => 199,
                'iso_code_2' => 'SR',
                'iso_code_3' => 'SUR',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            199 => 
            array (
                'id' => 200,
                'iso_code_2' => 'SJ',
                'iso_code_3' => 'SJM',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            200 => 
            array (
                'id' => 201,
                'iso_code_2' => 'SZ',
                'iso_code_3' => 'SWZ',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            201 => 
            array (
                'id' => 202,
                'iso_code_2' => 'SE',
                'iso_code_3' => 'SWE',
                'postcode_required' => 1,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            202 => 
            array (
                'id' => 203,
                'iso_code_2' => 'CH',
                'iso_code_3' => 'CHE',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            203 => 
            array (
                'id' => 204,
                'iso_code_2' => 'SY',
                'iso_code_3' => 'SYR',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            204 => 
            array (
                'id' => 205,
                'iso_code_2' => 'TW',
                'iso_code_3' => 'TWN',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            205 => 
            array (
                'id' => 206,
                'iso_code_2' => 'TJ',
                'iso_code_3' => 'TJK',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            206 => 
            array (
                'id' => 207,
                'iso_code_2' => 'TZ',
                'iso_code_3' => 'TZA',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            207 => 
            array (
                'id' => 208,
                'iso_code_2' => 'TH',
                'iso_code_3' => 'THA',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            208 => 
            array (
                'id' => 209,
                'iso_code_2' => 'TG',
                'iso_code_3' => 'TGO',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            209 => 
            array (
                'id' => 210,
                'iso_code_2' => 'TK',
                'iso_code_3' => 'TKL',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            210 => 
            array (
                'id' => 211,
                'iso_code_2' => 'TO',
                'iso_code_3' => 'TON',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            211 => 
            array (
                'id' => 212,
                'iso_code_2' => 'TT',
                'iso_code_3' => 'TTO',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            212 => 
            array (
                'id' => 213,
                'iso_code_2' => 'TN',
                'iso_code_3' => 'TUN',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            213 => 
            array (
                'id' => 214,
                'iso_code_2' => 'TR',
                'iso_code_3' => 'TUR',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            214 => 
            array (
                'id' => 215,
                'iso_code_2' => 'TM',
                'iso_code_3' => 'TKM',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            215 => 
            array (
                'id' => 216,
                'iso_code_2' => 'TC',
                'iso_code_3' => 'TCA',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            216 => 
            array (
                'id' => 217,
                'iso_code_2' => 'TV',
                'iso_code_3' => 'TUV',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            217 => 
            array (
                'id' => 218,
                'iso_code_2' => 'UG',
                'iso_code_3' => 'UGA',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            218 => 
            array (
                'id' => 219,
                'iso_code_2' => 'UA',
                'iso_code_3' => 'UKR',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            219 => 
            array (
                'id' => 220,
                'iso_code_2' => 'AE',
                'iso_code_3' => 'ARE',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            220 => 
            array (
                'id' => 221,
                'iso_code_2' => 'GB',
                'iso_code_3' => 'GBR',
                'postcode_required' => 1,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            221 => 
            array (
                'id' => 222,
                'iso_code_2' => 'US',
                'iso_code_3' => 'USA',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            222 => 
            array (
                'id' => 223,
                'iso_code_2' => 'UM',
                'iso_code_3' => 'UMI',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            223 => 
            array (
                'id' => 224,
                'iso_code_2' => 'UY',
                'iso_code_3' => 'URY',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            224 => 
            array (
                'id' => 225,
                'iso_code_2' => 'UZ',
                'iso_code_3' => 'UZB',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            225 => 
            array (
                'id' => 226,
                'iso_code_2' => 'VU',
                'iso_code_3' => 'VUT',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            226 => 
            array (
                'id' => 227,
                'iso_code_2' => 'VA',
                'iso_code_3' => 'VAT',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            227 => 
            array (
                'id' => 228,
                'iso_code_2' => 'VE',
                'iso_code_3' => 'VEN',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            228 => 
            array (
                'id' => 229,
                'iso_code_2' => 'VN',
                'iso_code_3' => 'VNM',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            229 => 
            array (
                'id' => 230,
                'iso_code_2' => 'VG',
                'iso_code_3' => 'VGB',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            230 => 
            array (
                'id' => 231,
                'iso_code_2' => 'VI',
                'iso_code_3' => 'VIR',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            231 => 
            array (
                'id' => 232,
                'iso_code_2' => 'WF',
                'iso_code_3' => 'WLF',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            232 => 
            array (
                'id' => 233,
                'iso_code_2' => 'EH',
                'iso_code_3' => 'ESH',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            233 => 
            array (
                'id' => 234,
                'iso_code_2' => 'YE',
                'iso_code_3' => 'YEM',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            234 => 
            array (
                'id' => 235,
                'iso_code_2' => 'CD',
                'iso_code_3' => 'COD',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            235 => 
            array (
                'id' => 236,
                'iso_code_2' => 'ZM',
                'iso_code_3' => 'ZMB',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            236 => 
            array (
                'id' => 237,
                'iso_code_2' => 'ZW',
                'iso_code_3' => 'ZWE',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            237 => 
            array (
                'id' => 238,
                'iso_code_2' => 'ME',
                'iso_code_3' => 'MNE',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            238 => 
            array (
                'id' => 239,
                'iso_code_2' => 'RS',
                'iso_code_3' => 'SRB',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            239 => 
            array (
                'id' => 240,
                'iso_code_2' => 'AX',
                'iso_code_3' => 'ALA',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            240 => 
            array (
                'id' => 241,
                'iso_code_2' => 'BQ',
                'iso_code_3' => 'BES',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            241 => 
            array (
                'id' => 242,
                'iso_code_2' => 'CW',
                'iso_code_3' => 'CUW',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            242 => 
            array (
                'id' => 243,
                'iso_code_2' => 'PS',
                'iso_code_3' => 'PSE',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            243 => 
            array (
                'id' => 244,
                'iso_code_2' => 'SS',
                'iso_code_3' => 'SSD',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            244 => 
            array (
                'id' => 245,
                'iso_code_2' => 'BL',
                'iso_code_3' => 'BLM',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            245 => 
            array (
                'id' => 246,
                'iso_code_2' => 'MF',
                'iso_code_3' => 'MAF',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            246 => 
            array (
                'id' => 247,
                'iso_code_2' => 'IC',
                'iso_code_3' => 'ICA',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            247 => 
            array (
                'id' => 248,
                'iso_code_2' => 'AC',
                'iso_code_3' => 'ASC',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            248 => 
            array (
                'id' => 249,
                'iso_code_2' => 'XK',
                'iso_code_3' => 'UNK',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            249 => 
            array (
                'id' => 250,
                'iso_code_2' => 'IM',
                'iso_code_3' => 'IMN',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            250 => 
            array (
                'id' => 251,
                'iso_code_2' => 'TA',
                'iso_code_3' => 'SHN',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            251 => 
            array (
                'id' => 252,
                'iso_code_2' => 'GG',
                'iso_code_3' => 'GGY',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
            252 => 
            array (
                'id' => 253,
                'iso_code_2' => 'JE',
                'iso_code_3' => 'JEY',
                'postcode_required' => 0,
                'active' => 1,
                'created_at' => '2025-07-16 18:15:58',
                'updated_at' => '2025-07-16 18:15:58',
            ),
        ));
        
        
    }
}