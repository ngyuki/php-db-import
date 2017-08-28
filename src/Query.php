<?php
namespace ngyuki\DbImport;

use Doctrine\DBAL\Connection;

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
     * @return int
     */
    public function delete($table)
    {
        return $this->conn->createQueryBuilder()
            ->delete($this->conn->quoteIdentifier($table))->execute();
    }
}
