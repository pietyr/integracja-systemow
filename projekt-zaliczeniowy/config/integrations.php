<?php

return [

    'gus' => [
        'base_url' => 'https://bdl.stat.gov.pl/api/v1',
        'request_delay_ms' => 250,
        'unit_level' => 0,
        'indicators' => [
            'average_wage' => [
                'name' => 'Przeciętne miesięczne wynagrodzenie brutto',
                'category' => 'wage',
                'unit' => 'zł',
                'gus_variable_id' => 64428,
            ],
            'minimum_wage' => [
                'name' => 'Płaca minimalna brutto',
                'category' => 'wage',
                'unit' => 'zł',
                'source' => 'manual',
                'manual_values' => [
                    2010 => 1317, 2011 => 1386, 2012 => 1500, 2013 => 1600,
                    2014 => 1680, 2015 => 1750, 2016 => 1850, 2017 => 2000,
                    2018 => 2100, 2019 => 2250, 2020 => 2600, 2021 => 2800,
                    2022 => 3010, 2023 => 3600, 2024 => 4242, 2025 => 4666,
                ],
            ],
            'pension' => [
                'name' => 'Przeciętna miesięczna emerytura brutto',
                'category' => 'benefit',
                'unit' => 'zł',
                'gus_variable_id' => 155058,
            ],
            'family_pension' => [
                'name' => 'Przeciętna renta rodzinna brutto',
                'category' => 'benefit',
                'unit' => 'zł',
                'gus_variable_id' => 155060,
            ],
            'inflation' => [
                'name' => 'Wskaźnik cen towarów i usług konsumpcyjnych (inflacja)',
                'category' => 'macro',
                'unit' => 'rok poprzedni=100',
                'gus_variable_id' => 217230,
            ],
            'gdp' => [
                'name' => 'Produkt krajowy brutto ogółem',
                'category' => 'macro',
                'unit' => 'mln zł',
                'gus_variable_id' => 458271,
            ],
            'gdp_growth' => [
                'name' => 'Dynamika PKB (rok poprzedni=100)',
                'category' => 'macro',
                'unit' => '-',
                'gus_variable_id' => 458272,
            ],
        ],
    ],

    'nytimes' => [
        'base_url' => 'https://api.nytimes.com/svc/archive/v1',
        // Limit API: 5 zapytań/min → 12 s między miesiącami w łańcuchu kolejki.
        'request_delay_seconds' => 12,
        // Limit API: 500 zapytań/dzień — zostawiamy margines bezpieczeństwa.
        'max_requests_per_day' => 450,
        // Zakres synchronizacji (zgodny ze wskaźnikami GUS 2010–2025):
        'from' => [
            'year' => 2025,
            'month' => 12,
        ],
        'until' => [
            'year' => 2010,
            'month' => 1,
        ],
        'keywords' => [
            'economy', 'inflation', 'pension', 'wage', 'gdp',
            'poland', 'europe', 'recession', 'employment', 'benefits',
        ],
        'max_articles_per_month' => 30,
    ],

];
