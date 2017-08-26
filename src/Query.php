<?php
namespace ngyuki\DbImport;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;

class Query
{
    /**
     * @var Connection
     */
    private $conn;

    /**
     * @var array
     */
    private $meta = [];

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
     * @param string $column
     * @return Column
     */
    private function column($table, $column)
    {
        if (isset($this->meta[$table]) === false) {
            $this->meta[$table] = $this->conn->getSchemaManager()->listTableColumns($table);
        }
        if (isset($this->meta[$table][$column]) === false) {
            throw new \RuntimeException(sprintf('Undefined column %s.%s', $table, $column));
        }
        return $this->meta[$table][$column];
    }

    /**
     * @param string $table
     * @param array|\Traversable $row
     * @return array
     */
    public function values($table, $row)
    {
        $values = [];
        foreach ($row as $column => $value) {
            if ($value instanceof EmptyValue) {
                if ($this->column($table, $column)->getNotnull()) {
                    $value = '';
                } else {
                    $value = null;
                }
            }
            $values[$column] = $value;
        }
        return $values;
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
     * @return int
     */
    public function delete($table)
    {
        return $this->conn->createQueryBuilder()
            ->delete($this->conn->quoteIdentifier($table))->execute();
    }
}
