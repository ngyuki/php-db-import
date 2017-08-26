<?php
$loader = null;

foreach (array(__DIR__ . '/../../../autoload.php', __DIR__ . '/../vendor/autoload.php') as $fn) {
    if (file_exists($fn)) {
        /** @noinspection PhpIncludeInspection */
        $loader = require $fn;
        break;
    }
}

if ($loader === null) {
    $cmd = basename($_SERVER['SCRIPT_FILENAME']);
    fputs(STDERR, "$cmd: unable to load composer auto loader." . PHP_EOL);
    exit(1);
}

$application = new \ngyuki\DbImport\Console\Application();
$application->run();
