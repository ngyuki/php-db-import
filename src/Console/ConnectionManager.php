<?php
namespace ngyuki\DbImport\Console;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PDO;

class ConnectionManager
{
    public function getConnection(array $config)
    {
        if (isset($config['connection'])) {
            $connection = $config['connection'];
            if (!$connection instanceof Connection) {
                throw new \DomainException(sprintf('$config["connection"] must be instance of %s', Connection::class));
            }
            return $config['connection'];
        }

        if (isset($config['db.params'])) {
            $params = $config['db.params'];
            if (!is_array($params)) {
                throw new \DomainException(sprintf('$config["db.params"] must be array'));
            }
            return DriverManager::getConnection($params);
        }

        if (isset($config['pdo'])) {
            $pdo = $config['pdo'];
            if (!$pdo instanceof PDO) {
                throw new \DomainException(sprintf('$config["pdo"] must be instance of %s', PDO::class));
            }
            return DriverManager::getConnection(['pdo' => $pdo]);
        }

        throw new \DomainException('config must be has "connection" or "db.params" or "pdo".');
    }
}
