<?php
/**
 * @link:http://www.zjhejiang.com/
 * @copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 *
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2018/12/4
 * Time: 9:33
 */

namespace app\models;


class DistrictArr
{
    public static function getArr()
    {
        return array(
            1 =>
                array(
                    'id' => 1,
                    'name' => '科特迪瓦',
                    'parent_id' => 0,
                    'level' => 'country',
                ),
            2 =>
                array(
                    'id' => 2,
                    'name' => 'ABIDJAN',
                    'parent_id' => 1,
                    'level' => 'province',
                ),
            3 =>
                array(
                    'id' => 3,
                    'name' => 'ABOBO',
                    'parent_id' => 2,
                    'level' => 'city',
                ),
            4 =>
                array(
                    'id' => 4,
                    'name' => '我不清楚',
                    'parent_id' => 3,
                    'level' => 'district',
                ),
            5 =>
                array(
                    'id' => 5,
                    'name' => 'Abobo-té',
                    'parent_id' => 3,
                    'level' => 'district',
                ),
            6 =>
                array(
                    'id' => 6,
                    'name' => 'Akéikoi',
                    'parent_id' => 3,
                    'level' => 'district',
                ),
            7 =>
                array(
                    'id' => 7,
                    'name' => 'Avocatier',
                    'parent_id' => 3,
                    'level' => 'district',
                ),
            8 =>
                array(
                    'id' => 8,
                    'name' => "N'dotré",
                    'parent_id' => 3,
                    'level' => 'district',
                ),
            9 =>
                array(
                    'id' => 9,
                    'name' => "PK18",
                    'parent_id' => 3,
                    'level' => 'district',
                ),
            10 =>
                array(
                    'id' => 10,
                    'name' => "其他区域",
                    'parent_id' => 3,
                    'level' => 'district',
                ),
            11 =>
                array(
                    'id' => 11,
                    'name' => "ADJAME",
                    'parent_id' => 2,
                    'level' => 'city',
                ),
            12 =>
                array(
                    'id' => 12,
                    'name' => "我不清楚",
                    'parent_id' => 11,
                    'level' => 'district',
                ),
            13 =>
                array(
                    'id' => 13,
                    'name' => "220 logements",
                    'parent_id' => 11,
                    'level' => 'district',
                ),
            14 =>
                array(
                    'id' => 14,
                    'name' => "Adjamé Nord",
                    'parent_id' => 11,
                    'level' => 'district',
                ),
            15 =>
                array(
                    'id' => 15,
                    'name' => "Adjamé Nord-est",
                    'parent_id' => 11,
                    'level' => 'district',
                ),
            16 =>
                array(
                    'id' => 16,
                    'name' => "Bromakoté",
                    'parent_id' => 11,
                    'level' => 'district',
                ),
            17 =>
                array(
                    'id' => 17,
                    'name' => "Dallas",
                    'parent_id' => 11,
                    'level' => 'district',
                ),
            18 =>
                array(
                    'id' => 18,
                    'name' => "Habitat extension",
                    'parent_id' => 11,
                    'level' => 'district',
                ),
            19 =>
                array(
                    'id' => 19,
                    'name' => "Indénié",
                    'parent_id' => 11,
                    'level' => 'district',
                ),
            20 =>
                array(
                    'id' => 20,
                    'name' => "Mairie I",
                    'parent_id' => 11,
                    'level' => 'district',
                ),
            21 =>
                array(
                    'id' => 21,
                    'name' => "Mairie II",
                    'parent_id' => 11,
                    'level' => 'district',
                ),
            22 =>
                array(
                    'id' => 22,
                    'name' => "Marie Thérèse",
                    'parent_id' => 11,
                    'level' => 'district',
                ),
            23 =>
                array(
                    'id' => 23,
                    'name' => "Mirador",
                    'parent_id' => 11,
                    'level' => 'district',
                ),
            24 =>
                array(
                    'id' => 24,
                    'name' => "Pallier",
                    'parent_id' => 11,
                    'level' => 'district',
                ),
            25 =>
                array(
                    'id' => 25,
                    'name' => "Quartier Ebrié",
                    'parent_id' => 11,
                    'level' => 'district',
                ),
            26 =>
                array(
                    'id' => 26,
                    'name' => "SODECI-FILTISAC",
                    'parent_id' => 11,
                    'level' => 'district',
                ),
            27 =>
                array(
                    'id' => 27,
                    'name' => "Village Ebrié",
                    'parent_id' => 11,
                    'level' => 'district',
                ),
            28 =>
                array(
                    'id' => 28,
                    'name' => "Williamsville I",
                    'parent_id' => 11,
                    'level' => 'district',
                ),
            29 =>
                array(
                    'id' => 29,
                    'name' => "Williamsville II",
                    'parent_id' => 11,
                    'level' => 'district',
                ),
            30 =>
                array(
                    'id' => 30,
                    'name' => "Williamsville III",
                    'parent_id' => 11,
                    'level' => 'district',
                ),
            31 =>
                array(
                    'id' => 31,
                    'name' => "其他区域",
                    'parent_id' => 11,
                    'level' => 'district',
                ),
            32 =>
                array(
                    'id' => 32,
                    'name' => "ATTECOUBE",
                    'parent_id' => 2,
                    'level' => 'city',
                ),
            33 =>
                array(
                    'id' => 33,
                    'name' => "我不清楚",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            34 =>
                array(
                    'id' => 34,
                    'name' => "Agban Atté",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            35 =>
                array(
                    'id' => 35,
                    'name' => "Akélié",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            36 =>
                array(
                    'id' => 36,
                    'name' => "Attécoubé 3",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            37 =>
                array(
                    'id' => 37,
                    'name' => "Awa",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            38 =>
                array(
                    'id' => 38,
                    'name' => "Bidjanté",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            39 =>
                array(
                    'id' => 39,
                    'name' => "Camp Douane",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            40 =>
                array(
                    'id' => 40,
                    'name' => "Cantonnement Forestier",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            41 =>
                array(
                    'id' => 41,
                    'name' => "Cité Fairmont 1",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            42 =>
                array(
                    'id' => 42,
                    'name' => "Cité Fairmont 2",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            43 =>
                array(
                    'id' => 43,
                    'name' => "Déindé",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            44 =>
                array(
                    'id' => 44,
                    'name' => "Djéné Ecaré",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            45 =>
                array(
                    'id' => 45,
                    'name' => "Douagoville",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            46 =>
                array(
                    'id' => 46,
                    'name' => "Ecole",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            47 =>
                array(
                    'id' => 47,
                    'name' => "Ecole Forestière",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            48 =>
                array(
                    'id' => 48,
                    'name' => "Espoir",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            49 =>
                array(
                    'id' => 49,
                    'name' => "Fromager",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            50 =>
                array(
                    'id' => 50,
                    'name' => "Gbebouto",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            51 =>
                array(
                    'id' => 51,
                    'name' => "Jean-Paul 2",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            52 =>
                array(
                    'id' => 52,
                    'name' => "Jérusalem 1",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            53 =>
                array(
                    'id' => 53,
                    'name' => "Jérusalem 2",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            54 =>
                array(
                    'id' => 54,
                    'name' => "Jérusalem 3",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            55 =>
                array(
                    'id' => 55,
                    'name' => "Jérusalem Résidentiel",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            56 =>
                array(
                    'id' => 56,
                    'name' => "La Paix",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            57 =>
                array(
                    'id' => 57,
                    'name' => "Lackman",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            58 =>
                array(
                    'id' => 58,
                    'name' => "Lagune",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            59 =>
                array(
                    'id' => 59,
                    'name' => "Mosquée",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            60 =>
                array(
                    'id' => 60,
                    'name' => "Saint-Joseph",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            61 =>
                array(
                    'id' => 61,
                    'name' => "Santé 3 Extension",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            62 =>
                array(
                    'id' => 62,
                    'name' => "Santé 3 Résidentiel 1",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            63 =>
                array(
                    'id' => 63,
                    'name' => "Santé 3 Résidentiel 2",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            64 =>
                array(
                    'id' => 64,
                    'name' => "Santé Carrefour",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            65 =>
                array(
                    'id' => 65,
                    'name' => "Santé Ecole",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            66 =>
                array(
                    'id' => 66,
                    'name' => "Sebroko",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            67 =>
                array(
                    'id' => 67,
                    'name' => "其他区域",
                    'parent_id' => 32,
                    'level' => 'district',
                ),
            68 =>
                array(
                    'id' => 68,
                    'name' => "COCODY",
                    'parent_id' => 2,
                    'level' => 'city',
                ),
            69 =>
                array(
                    'id' => 69,
                    'name' => "我不清楚",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            70 =>
                array(
                    'id' => 70,
                    'name' => "7ème tranche",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            71 =>
                array(
                    'id' => 71,
                    'name' => "8ème tranche",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            72 =>
                array(
                    'id' => 72,
                    'name' => "9ème tranche",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            73 =>
                array(
                    'id' => 73,
                    'name' => "Agban Gendamerie",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            74 =>
                array(
                    'id' => 74,
                    'name' => "Aghien",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            75 =>
                array(
                    'id' => 75,
                    'name' => "Akouédo ancien",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            76 =>
                array(
                    'id' => 76,
                    'name' => "Akouédo nouveau",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            77 =>
                array(
                    'id' => 77,
                    'name' => "Akouédo village",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            78 =>
                array(
                    'id' => 78,
                    'name' => "Allabra sogephia",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            79 =>
                array(
                    'id' => 79,
                    'name' => "Ambassade",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            80 =>
                array(
                    'id' => 80,
                    'name' => "Angré",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            81 =>
                array(
                    'id' => 81,
                    'name' => "Anono",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            82 =>
                array(
                    'id' => 82,
                    'name' => "Attoban",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            83 =>
                array(
                    'id' => 83,
                    'name' => "Blockauss",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            84 =>
                array(
                    'id' => 84,
                    'name' => "Bonoumin",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            85 =>
                array(
                    'id' => 85,
                    'name' => "Canebière",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            86 =>
                array(
                    'id' => 86,
                    'name' => "Centre",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            87 =>
                array(
                    'id' => 87,
                    'name' => "CHU",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            88 =>
                array(
                    'id' => 88,
                    'name' => "Cité des arts",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            89 =>
                array(
                    'id' => 89,
                    'name' => "Cité des cadres",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            90 =>
                array(
                    'id' => 90,
                    'name' => "Cocody village",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            91 =>
                array(
                    'id' => 91,
                    'name' => "Copraci coprim",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            92 =>
                array(
                    'id' => 92,
                    'name' => "Danga",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            93 =>
                array(
                    'id' => 93,
                    'name' => "Djorogobité",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            94 =>
                array(
                    'id' => 94,
                    'name' => "E.E.C.i. Riviera",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            95 =>
                array(
                    'id' => 95,
                    'name' => "E.N.A",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            96 =>
                array(
                    'id' => 96,
                    'name' => "Ecole de gendarmerie",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            97 =>
                array(
                    'id' => 97,
                    'name' => "II plateaux 1 AE",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            98 =>
                array(
                    'id' => 98,
                    'name' => "II plateaux 2",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            99 =>
                array(
                    'id' => 99,
                    'name' => "II plateaux Est",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            100 =>
                array(
                    'id' => 100,
                    'name' => "Lycée Technique",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            101 =>
                array(
                    'id' => 101,
                    'name' => "M’badon",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            102 =>
                array(
                    'id' => 102,
                    'name' => "M’pouto",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            103 =>
                array(
                    'id' => 103,
                    'name' => "Opération palmeraie génie 2000",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            104 =>
                array(
                    'id' => 104,
                    'name' => "Plaingué",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            105 =>
                array(
                    'id' => 105,
                    'name' => "Plateau dokui",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            106 =>
                array(
                    'id' => 106,
                    'name' => "Riviera 3-4-5",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            107 =>
                array(
                    'id' => 107,
                    'name' => "Riviera Golf 1",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            108 =>
                array(
                    'id' => 108,
                    'name' => "Riviera Golf 2",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            109 =>
                array(
                    'id' => 109,
                    'name' => "Riviera palmeraie",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            110 =>
                array(
                    'id' => 110,
                    'name' => "SIDECI Riviera",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            111 =>
                array(
                    'id' => 111,
                    'name' => "SIDECI zoo",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            112 =>
                array(
                    'id' => 112,
                    'name' => "Sogephia Riviera II",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            113 =>
                array(
                    'id' => 113,
                    'name' => "Sopim valon",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            114 =>
                array(
                    'id' => 114,
                    'name' => "Université",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            115 =>
                array(
                    'id' => 115,
                    'name' => "Val doyen",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            116 =>
                array(
                    'id' => 116,
                    'name' => "其他区域",
                    'parent_id' => 68,
                    'level' => 'district',
                ),
            117 =>
                array(
                    'id' => 117,
                    'name' => "KOUMASSI",
                    'parent_id' => 2,
                    'level' => 'city',
                ),
            118 =>
                array(
                    'id' => 118,
                    'name' => "我不清楚",
                    'parent_id' => 117,
                    'level' => 'district',
                ),
            119 =>
                array(
                    'id' => 119,
                    'name' => "Aklomiabla",
                    'parent_id' => 117,
                    'level' => 'district',
                ),
            120 =>
                array(
                    'id' => 120,
                    'name' => "Grand Campement",
                    'parent_id' => 117,
                    'level' => 'district',
                ),
            121 =>
                array(
                    'id' => 121,
                    'name' => "Houphouet Boigny 1 & 2",
                    'parent_id' => 117,
                    'level' => 'district',
                ),
            122 =>
                array(
                    'id' => 122,
                    'name' => "Pengolin",
                    'parent_id' => 117,
                    'level' => 'district',
                ),
            123 =>
                array(
                    'id' => 123,
                    'name' => "Quartier Divo",
                    'parent_id' => 117,
                    'level' => 'district',
                ),
            124 =>
                array(
                    'id' => 124,
                    'name' => "Remblais",
                    'parent_id' => 117,
                    'level' => 'district',
                ),
            125 =>
                array(
                    'id' => 125,
                    'name' => "SICOGI",
                    'parent_id' => 117,
                    'level' => 'district',
                ),
            126 =>
                array(
                    'id' => 126,
                    'name' => "Sobrici",
                    'parent_id' => 117,
                    'level' => 'district',
                ),
            127 =>
                array(
                    'id' => 127,
                    'name' => "SOGEPHIA",
                    'parent_id' => 117,
                    'level' => 'district',
                ),
            128 =>
                array(
                    'id' => 128,
                    'name' => "SOPIM",
                    'parent_id' => 117,
                    'level' => 'district',
                ),
            129 =>
                array(
                    'id' => 129,
                    'name' => "Yapokro",
                    'parent_id' => 117,
                    'level' => 'district',
                ),
            130 =>
                array(
                    'id' => 130,
                    'name' => "Zoé Bruno",
                    'parent_id' => 117,
                    'level' => 'district',
                ),
            131 =>
                array(
                    'id' => 131,
                    'name' => "其他区域",
                    'parent_id' => 117,
                    'level' => 'district',
                ),
            132 =>
                array(
                    'id' => 132,
                    'name' => "MARCORY",
                    'parent_id' => 2,
                    'level' => 'city',
                ),
            133 =>
                array(
                    'id' => 133,
                    'name' => "我不清楚",
                    'parent_id' => 132,
                    'level' => 'district',
                ),
            134 =>
                array(
                    'id' => 134,
                    'name' => "Abety",
                    'parent_id' => 132,
                    'level' => 'district',
                ),
            135 =>
                array(
                    'id' => 135,
                    'name' => "Abia-Koumassi",
                    'parent_id' => 132,
                    'level' => 'district',
                ),
            136 =>
                array(
                    'id' => 136,
                    'name' => "Adeimin",
                    'parent_id' => 132,
                    'level' => 'district',
                ),
            137 =>
                array(
                    'id' => 137,
                    'name' => "Aliodan",
                    'parent_id' => 132,
                    'level' => 'district',
                ),
            138 =>
                array(
                    'id' => 138,
                    'name' => "Anoumanbo",
                    'parent_id' => 132,
                    'level' => 'district',
                ),
            139 =>
                array(
                    'id' => 139,
                    'name' => "Biétry",
                    'parent_id' => 132,
                    'level' => 'district',
                ),
            140 =>
                array(
                    'id' => 140,
                    'name' => "Champroux",
                    'parent_id' => 132,
                    'level' => 'district',
                ),
            141 =>
                array(
                    'id' => 141,
                    'name' => "Gnanzoua",
                    'parent_id' => 132,
                    'level' => 'district',
                ),
            142 =>
                array(
                    'id' => 142,
                    'name' => "Hibiscus",
                    'parent_id' => 132,
                    'level' => 'district',
                ),
            143 =>
                array(
                    'id' => 143,
                    'name' => "Jean Batiste Mockey",
                    'parent_id' => 132,
                    'level' => 'district',
                ),
            144 =>
                array(
                    'id' => 144,
                    'name' => "Kablan Brou Fulgence",
                    'parent_id' => 132,
                    'level' => 'district',
                ),
            145 =>
                array(
                    'id' => 145,
                    'name' => "Konan Raphael",
                    'parent_id' => 132,
                    'level' => 'district',
                ),
            146 =>
                array(
                    'id' => 146,
                    'name' => "Marie-koré",
                    'parent_id' => 132,
                    'level' => 'district',
                ),
            147 =>
                array(
                    'id' => 147,
                    'name' => "Quartier résidentiel",
                    'parent_id' => 132,
                    'level' => 'district',
                ),
            148 =>
                array(
                    'id' => 148,
                    'name' => "Zone 4 C",
                    'parent_id' => 132,
                    'level' => 'district',
                ),
            149 =>
                array(
                    'id' => 149,
                    'name' => "其他区域",
                    'parent_id' => 132,
                    'level' => 'district',
                ),
            150 =>
                array(
                    'id' => 150,
                    'name' => "PLATEAU",
                    'parent_id' => 2,
                    'level' => 'city',
                ),
            151 =>
                array(
                    'id' => 151,
                    'name' => "我不清楚",
                    'parent_id' => 150,
                    'level' => 'district',
                ),
            152 =>
                array(
                    'id' => 152,
                    'name' => "Plateau",
                    'parent_id' => 150,
                    'level' => 'district',
                ),
            153 =>
                array(
                    'id' => 153,
                    'name' => "其他区域",
                    'parent_id' => 150,
                    'level' => 'district',
                ),
            154 =>
                array(
                    'id' => 154,
                    'name' => "PORT-BOUET",
                    'parent_id' => 2,
                    'level' => 'city',
                ),
            155 =>
                array(
                    'id' => 155,
                    'name' => "我不清楚",
                    'parent_id' => 154,
                    'level' => 'district',
                ),
            156 =>
                array(
                    'id' => 156,
                    'name' => "Abouabou",
                    'parent_id' => 154,
                    'level' => 'district',
                ),
            157 =>
                array(
                    'id' => 157,
                    'name' => "Adjoufou",
                    'parent_id' => 154,
                    'level' => 'district',
                ),
            158 =>
                array(
                    'id' => 158,
                    'name' => "Derrière Wharf",
                    'parent_id' => 154,
                    'level' => 'district',
                ),
            159 =>
                array(
                    'id' => 159,
                    'name' => "Mafiblé",
                    'parent_id' => 154,
                    'level' => 'district',
                ),
            160 =>
                array(
                    'id' => 160,
                    'name' => "其他区域",
                    'parent_id' => 154,
                    'level' => 'district',
                ),
            161 =>
                array(
                    'id' => 161,
                    'name' => "TREICHVILLE",
                    'parent_id' => 2,
                    'level' => 'city',
                ),
            162 =>
                array(
                    'id' => 162,
                    'name' => "我不清楚",
                    'parent_id' => 161,
                    'level' => 'district',
                ),
            163 =>
                array(
                    'id' => 163,
                    'name' => "Apollo",
                    'parent_id' => 161,
                    'level' => 'district',
                ),
            164 =>
                array(
                    'id' => 164,
                    'name' => "Arras",
                    'parent_id' => 161,
                    'level' => 'district',
                ),
            165 =>
                array(
                    'id' => 165,
                    'name' => "Biafra",
                    'parent_id' => 161,
                    'level' => 'district',
                ),
            166 =>
                array(
                    'id' => 166,
                    'name' => "Belleville",
                    'parent_id' => 161,
                    'level' => 'district',
                ),
            167 =>
                array(
                    'id' => 167,
                    'name' => "其他区域",
                    'parent_id' => 161,
                    'level' => 'district',
                ),
            168 =>
                array(
                    'id' => 168,
                    'name' => "YOPOUGON",
                    'parent_id' => 2,
                    'level' => 'city',
                ),
            169 =>
                array(
                    'id' => 169,
                    'name' => "我不清楚",
                    'parent_id' => 168,
                    'level' => 'district',
                ),
            170 =>
                array(
                    'id' => 170,
                    'name' => "Adiapodoumé",
                    'parent_id' => 168,
                    'level' => 'district',
                ),
            171 =>
                array(
                    'id' => 171,
                    'name' => "Andokoi",
                    'parent_id' => 168,
                    'level' => 'district',
                ),
            172 =>
                array(
                    'id' => 172,
                    'name' => "Azito",
                    'parent_id' => 168,
                    'level' => 'district',
                ),
            173 =>
                array(
                    'id' => 173,
                    'name' => "Béago",
                    'parent_id' => 168,
                    'level' => 'district',
                ),
            174 =>
                array(
                    'id' => 174,
                    'name' => "Camp militaire",
                    'parent_id' => 168,
                    'level' => 'district',
                ),
            175 =>
                array(
                    'id' => 175,
                    'name' => "Gesco",
                    'parent_id' => 168,
                    'level' => 'district',
                ),
            176 =>
                array(
                    'id' => 176,
                    'name' => "Île boulay",
                    'parent_id' => 168,
                    'level' => 'district',
                ),
            177 =>
                array(
                    'id' => 177,
                    'name' => "Maroc",
                    'parent_id' => 168,
                    'level' => 'district',
                ),
            178 =>
                array(
                    'id' => 178,
                    'name' => "Niangon Adjamé",
                    'parent_id' => 168,
                    'level' => 'district',
                ),
            179 =>
                array(
                    'id' => 179,
                    'name' => "Niangon Attié",
                    'parent_id' => 168,
                    'level' => 'district',
                ),
            180 =>
                array(
                    'id' => 180,
                    'name' => "Niangon Lokoa",
                    'parent_id' => 168,
                    'level' => 'district',
                ),
            181 =>
                array(
                    'id' => 181,
                    'name' => "Niangon Sud",
                    'parent_id' => 168,
                    'level' => 'district',
                ),
            182 =>
                array(
                    'id' => 182,
                    'name' => "Nouveau Quartier",
                    'parent_id' => 168,
                    'level' => 'district',
                ),
            183 =>
                array(
                    'id' => 183,
                    'name' => "P.K.17",
                    'parent_id' => 168,
                    'level' => 'district',
                ),
            184 =>
                array(
                    'id' => 184,
                    'name' => "Port-Bouët 2",
                    'parent_id' => 168,
                    'level' => 'district',
                ),
            185 =>
                array(
                    'id' => 185,
                    'name' => "Selmer",
                    'parent_id' => 168,
                    'level' => 'district',
                ),
            186 =>
                array(
                    'id' => 186,
                    'name' => "Sideci",
                    'parent_id' => 168,
                    'level' => 'district',
                ),
            187 =>
                array(
                    'id' => 187,
                    'name' => "Sogefiha",
                    'parent_id' => 168,
                    'level' => 'district',
                ),
            188 =>
                array(
                    'id' => 188,
                    'name' => "Toits Rouges",
                    'parent_id' => 168,
                    'level' => 'district',
                ),
            189 =>
                array(
                    'id' => 189,
                    'name' => "Wassakara",
                    'parent_id' => 168,
                    'level' => 'district',
                ),
            190 =>
                array(
                    'id' => 190,
                    'name' => "Yopougon Attié",
                    'parent_id' => 168,
                    'level' => 'district',
                ),
            191 =>
                array(
                    'id' => 191,
                    'name' => "Yopougon Kouté",
                    'parent_id' => 168,
                    'level' => 'district',
                ),
            192 =>
                array(
                    'id' => 192,
                    'name' => "Yopougon Santé",
                    'parent_id' => 168,
                    'level' => 'district',
                ),
            193 =>
                array(
                    'id' => 193,
                    'name' => "Zone industrielle",
                    'parent_id' => 168,
                    'level' => 'district',
                ),
            194 =>
                array(
                    'id' => 194,
                    'name' => "其他区域",
                    'parent_id' => 168,
                    'level' => 'district',
                ),
            195 =>
                array(
                    'id' => 195,
                    'name' => '其他区域',
                    'parent_id' => 1,
                    'level' => 'province',
                ),
        );
    }

    public static function getDiffCityDistrict($city_name)
    {
        $list = [];
        return isset($list[$city_name]) ? $list[$city_name] : null;
    }

    // 数组排序 先按parent_id正序再按id正序进行排列
    public static function getSort($arr)
    {
        uasort($arr, function ($a, $b) {
            $a_p = intval($a['parent_id']);
            $b_p = intval($b['parent_id']);
            if ($a_p == $b_p) {
                $a_id = intval($a['id']);
                $b_id = intval($b['id']);
                if ($a_id == $b_id) {
                    return 0;
                }
                return ($a_id < $b_id) ? -1 : 1;
            }
            return ($a_p < $b_p) ? -1 : 1;
        });
        echo "<pre>";
        var_export($arr);
        echo "</pre>";

        exit();
    }

    /**
     * 获取已父级id为$parent_id为根节点的树型结构数组
     * @param array $arr 省市区数据
     * @param string $level 不需要的数据的level，当前等级且包含其下级都排除
     * @return array
     */
    public static function getList(&$arr, $level = null)
    {
        $treeData = [];// 保存结果
        $catList = $arr;
        foreach ($catList as &$item) {
            if ($level && $item['level'] == $level) {
                continue;
            }
            $parent_id = $item['parent_id'];
            if (isset($catList[$parent_id]) && !empty($catList[$parent_id])) {// 肯定是子分类
                $catList[$parent_id]['list'][] = &$catList[$item['id']];
            } else {// 肯定是一级分类
                $treeData[] = &$catList[$item['id']];
            }
        }
        unset($item);
        return $treeData[0]['list'];
    }

    // 根据id获取信息
    public static function getDistrict($param)
    {
        if (is_array($param)) {
            $id = $param['id'];
        } else {
            $id = $param;
        }
        $arr = self::getArr();
        $list = $arr[$id];
        $str = \Yii::$app->serializer->encode($list);
        return \Yii::$app->serializer->decode($str);
    }

    // 根据指定的key=>value查找需要的数组
    public static function getInfo($param)
    {
        $newParam = [];
        foreach ($param as $key => $value) {
            $newParam[0] = $key;
            $newParam[1] = $value;
        }
        $arr = self::getArr();
        $list = array_filter($arr, function ($v) use ($newParam) {
            return $v[$newParam[0]] == $newParam[1];
        });
        $str = \Yii::$app->serializer->encode($list);
        return \Yii::$app->serializer->decode($str);
    }

    // 运费规则、起送规则、包邮规则
    public static function getRules()
    {
        $arr = self::getArr();
        $empty = [];
        $emptyPointer = &$empty;
        $ok = false;
        foreach ($arr as $index => &$item) {
            if ($item['parent_id'] == 1) {
                $okCity = false;
                $data = [
                    'id' => $item['id'],
                    'name' => $item['name']
                ];
                $data['show'] = false;
                $data['city'] = [];
                $dataPointer = &$data['city'];
                foreach ($arr as $key => $value) {
                    if ($value['parent_id'] == $index) {
                        $okCity = true;
                        $dataPointer[] = [
                            'id' => $value['id'],
                            'name' => $value['name'],
                            'show' => false
                        ];
                    }
                    if ($okCity && $value['parent_id'] != $index) {
                        break;
                    }
                }
                array_push($emptyPointer, $data);
                $ok = true;
            }
            if ($ok && $item['parent_id'] != 1) {
                break;
            }
        }

        return $empty;
    }

    // 微信获取地址
    public static function getWechatDistrict($province_name, $city_name, $county_name)
    {
        $arr = self::getArr();
        $ok = false;
        $res = [
            'code' => 0,
            'msg' => '',
            'data' => [
                'district' => [

                ]
            ]
        ];
        $county = [];
        foreach ($arr as $item) {
            if ($item['name'] == $county_name && $item['level'] == 'district') {
                $county = $item;
                $city = $arr[$item['parent_id']];
                if (isset($arr[$county['parent_id']]) && $city['name'] == $city_name) {
                    $province = $arr[$city['parent_id']];
                    if (isset($arr[$city['parent_id']]) && $province['name'] = $province_name) {
                        $ok = true;
                        break;
                    }
                }
            }
        }
        if (!$ok) {
            $diff_district = self::getDiffCityDistrict($city_name);
            $res['data']['district'] = [
                'province' => [
                    'id' => 3268,
                    'name' => '其他',
                ],
                'city' => [
                    'id' => 3269,
                    'name' => '其他',
                ],
                'district' => [
                    'id' => 3270,
                    'name' => '其他',
                ],
            ];
            if ($diff_district) {
                $res['data']['district'] = $diff_district;
            }
            return $res;
        }

        $res['data']['district'] = [
            'province' => [
                'id' => $province['id'],
                'name' => $province['name']
            ],
            'city' => [
                'id' => $city['id'],
                'name' => $city['name']
            ],
            'district' => [
                'id' => $county['id'],
                'name' => $county['name']
            ]
        ];

        return $res;
    }

    public static function getTerritorial()
    {
        $data = \Yii::$app->cache->get('territorial_list');
        if ($data) {
            return $data;
        }
        $arr = self::getArr();
        $treeData = [];// 保存结果
        $catList = &$arr;
        foreach ($catList as &$item) {
            $item['selected'] = false;
            $item['show'] = false;
            $parent_id = $item['parent_id'];
            if (isset($catList[$parent_id]) && !empty($catList[$parent_id])) {// 肯定是子分类
                $catList[$parent_id]['list'][] = &$catList[$item['id']];
            } else {// 肯定是一级分类
                $treeData[] = &$catList[$item['id']];
            }
        }
        unset($item);
        $data = $treeData[0]['list'];
        \Yii::$app->cache->set('territorial_list', $data);
        return $data;
    }
}