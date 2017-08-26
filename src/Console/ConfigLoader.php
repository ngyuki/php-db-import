<?php
namespace ngyuki\DbImport\Console;

class ConfigLoader
{
    public function load($path)
    {
        if (strlen($path) === 0) {
            $config = (new Configure())->get();
            if ($config) {
                return $config;
            }
        }

        $file = $this->resolve($path);

        /** @noinspection PhpIncludeInspection */
        return include $file;
    }

    private function resolve($path)
    {
        if (strlen($path) === 0) {
            $path = getenv('PHP_DB_IMPORT_CONFIG');
        }

        if (strlen($path) === 0) {
            $path = getcwd();
        }

        if (is_file($path)) {
            return $path;
        }

        if (is_dir($path)) {

            $files = array(
                'db-import.config.php',
                'db-import.config.php.dist',
            );

            $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

            foreach ($files as $file) {
                if (file_exists($path . $file)) {
                    return $path . $file;
                }
            }

            throw new \RuntimeException(
                sprintf(
                    'Unable resolve config.' . PHP_EOL .
                    'Not found "%s" or "%s" in "%s".',
                    'db-import.config.php',
                    'db-import.config.php.dist',
                    $path
                )
            );
        }

        throw new \RuntimeException(sprintf('Unable resolve config from "%s".', $path));
    }
}
