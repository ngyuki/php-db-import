<?php
namespace ngyuki\DbImport;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class Query
{
    /**
     * @var Connection
     */
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    /**
     * @param string $sql
     */
    public function exec($sql)
    {
        $this->conn->exec($sql);
    }

    /**
     * @param string $table
     * @param array $values
     * @return int
     */
    public function insert($table, $values)
    {
        $data = [];
        foreach ($values as $column => $value) {
            $data[$this->conn->quoteIdentifier($column)] = $value;
        }
        return $this->conn
            ->insert($this->conn->quoteIdentifier($table), $data);
    }

    /**
     * @param string $table
     * @param array $values
     * @return int
     */
    public function overwrite($table, $values)
    {
        try {
            return $this->insert($table, $values);
        } catch (UniqueConstraintViolationException $ex) {
            return $this->updateByUnique($table, $values);
        }
    }

    private function updateByUnique($table, $values)
    {
        $indexes = $this->conn->getSchemaManager()->listTableIndexes($table);

        foreach ($indexes as $index) {
            if (!$index->isPrimary() && !$index->isUnique()) {
                continue;
            }

            $ok = true;
            $columns = $index->getColumns();

            foreach ($columns as $name) {
                if (!isset($values[$name])) {
                    $ok = false;
                    break;
                }
            }

            if (!$ok) {
                continue;
            }

            $columns = array_flip($columns);
            $identifier = [];
            $data = [];
            foreach ($values as $column => $value) {
                if (isset($columns[$column])) {
                    $identifier[$this->conn->quoteIdentifier($column)] = $value;
                } else {
                    $data[$this->conn->quoteIdentifier($column)] = $value;
                }
            }

            return $this->conn->update($table, $data, $identifier);
        }

        throw new \RuntimeException("Unable overwrite \"$table\"");
    }

    /**
     * @param string $table
     * @return int
     */
    public function delete($table)
    {
        return $this->conn->createQueryBuilder()
            ->delete($this->conn->quoteIdentifier($table))->execute();
    }
}
