<?php
return [
    'file_list' => [],

    'exclude_file_regex' => '#^vendor/.*/(tests?)/#i',

    'exclude_file_list' => [
        'vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Worksheet/AutoFilter.php'
    ],

    'directory_list' => [
        'src/',
        'vendor/doctrine/dbal/',
        'vendor/symfony/yaml/',
        'vendor/phpoffice/phpspreadsheet/',
    ],

    'exclude_analysis_directory_list' => [
        'vendor/'
    ],
];
