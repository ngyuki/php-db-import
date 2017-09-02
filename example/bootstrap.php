<?php
///
// CLI の設定ファイルについて
//
// カレントディレクトリに下記のファイルを作成します（↑が優先）。
//
// example/db-import.config.php
// example/db-import.config.php.dist
//
// あるいは composer の autoload-dev.files で指定したファイルで、
// 下記のように Configure::register を実行しておけば設定ファイルは不要です。

\ngyuki\DbImport\Console\Configure::register(function () {
    if (file_exists(__DIR__ . '/config.php')) {
        return require __DIR__ . '/config.php';
    }
    return require __DIR__ . '/config.php.dist';
});
