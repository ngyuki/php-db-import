<?php
return [
    'file_list' => [],

    'exclude_file_regex' => '@^vendor/.*/(tests|Tests|test|Test)/@',

    'exclude_file_list' => [],

    'directory_list' => [
        'src/',
        'vendor/doctrine/dbal/',
        'vendor/symfony/console/',
        'vendor/symfony/yaml/',
        'vendor/phpoffice/phpspreadsheet/',
    ],

    "exclude_analysis_directory_list" => [
        'vendor/'
    ],
];
