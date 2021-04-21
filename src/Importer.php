<?php
namespace ngyuki\DbImport;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use LogicException;
use ngyuki\DbImport\DataSet\DataSetInterface;
use ngyuki\DbImport\DataSet\ExcelDataSet;
use ngyuki\DbImport\DataSet\PhpFileDataSet;
use ngyuki\DbImport\DataSet\YamlDataSet;
use ngyuki\DbImport\Exception\DatabaseException;
use Throwable;

class Importer
{
    /**
     * @var Connection
     */
    private $conn;

    /**
     * @var Query
     */
    private $query;

    /**
     * @var bool
     */
    private $delete;

    /**
     * @var bool
     */
    private $recursive;

    /**
     * @var bool
     */
    private $overwrite;

    /**
     * @var string[]
     */
    private $before = [];

    /**
     * @var string[]
     */
    private $after = [];

    /**
     * @var DataSetInterface[]
     */
    private $datalist = [];

    public function __construct(Connection $connection)
    {
        $this->conn = $connection;
        $this->query = new Query($connection);
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->conn;
    }

    /**
     * @param bool $val
     * @param bool $recursive
     * @return static
     */
    public function useDelete($val, $recursive = false)
    {
        assert(!$recursive || $val);

        $obj = clone $this;
        $obj->delete = $val;
        $obj->recursive = $val ? $recursive : false;
        return $obj;
    }

    /**
     * @param bool $val
     * @return static
     */
    public function useOverwrite($val)
    {
        $obj = clone $this;
        $obj->overwrite = $val;
        return $obj;
    }

    /**
     * @param string|string[] $sql
     * @return static
     */
    public function addBeforeSql($sql)
    {
        $obj = clone $this;
        $obj->before = array_merge($obj->before, (array)$sql);
        return $obj;
    }

    /**
     * @param string|string[] $sql
     * @return static
     */
    public function addAfterSql($sql)
    {
        $obj = clone $this;
        $obj->after = array_merge($obj->after, (array)$sql);
        return $obj;
    }

    /**
     * @param string[] $files
     * @return static
     */
    public function addFiles(array $files)
    {
        $extensions = [
            'php' => function ($file) {
                return new PhpFileDataSet($file);
            },
            'yml' => function ($file) {
                return new YamlDataSet($file);
            },
            'yaml' => function ($file) {
                return new YamlDataSet($file);
            },
            'xlsx' => function ($file) {
                return new ExcelDataSet($file);
            },
        ];

        $list = [];

        foreach ($files as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (!isset($extensions[$ext])) {
                throw new LogicException(sprintf(
                    'Unknown extension file "%s"',
                    $file
                ));
            }
            $loader = $extensions[$ext];
            $list[] = $loader($file);
        }

        $obj = clone $this;
        $obj->datalist = array_merge($obj->datalist, $list);
        return $obj;
    }

    /**
     * @param DataSetInterface $data
     * @return static
     */
    public function addDataSet(DataSetInterface $data)
    {
        $obj = clone $this;
        $obj->datalist[] = $data;
        return $obj;
    }

    public function import()
    {
        $tables = [];

        foreach ($this->datalist as $dataSet) {
            foreach ($dataSet->getData($this) as $table => $rows) {
                $tables[$table] = [];
                foreach ($rows as $row) {
                    $tables[$table][] = $row;
                }
            }
        }

        $this->importTables($tables);
    }

    private function importTables(array $tables)
    {
        try {
            foreach ($this->before as $sql) {
                $this->query->exec($sql);
            }
            if ($this->delete) {
                $this->down($tables);
            }
            $this->up($tables);
            foreach ($this->after as $sql) {
                $this->query->exec($sql);
            }
        } catch (DBALException $ex) {
            // DB エラーでは $previous を切る
            throw new DatabaseException($ex->getMessage(), $ex->getCode());
        }
    }

    private function up(array $tables)
    {
        foreach ($tables as $table => $rows) {
            foreach ($rows as $row) {
                try {
                    if ($this->overwrite) {
                        $this->query->overwrite($table, $row);
                    } else {
                        $this->query->insert($table, $row);
                    }
                } catch (Throwable $ex) {
                    if ($row instanceof DataRow) {
                        throw new DatabaseException(
                            sprintf(
                                '%s in %s ... %s',
                                $ex->getMessage(),
                                $row->getLocation(),
                                $this->pretty($row)
                            ),
                            $ex->getCode()
                        );
                    } else {
                        throw new DatabaseException(
                            sprintf(
                                '%s in [%s] ... %s',
                                $ex->getMessage(),
                                $table,
                                $this->pretty($row)
                            ),
                            $ex->getCode()
                        );
                    }
                }
            }
        }
    }

    private function down(array $tables)
    {
        $tables = array_reverse(array_keys($tables));

        if ($this->recursive) {
            $this->query->visitRecursive($tables, function ($table) {
                try {
                    $this->query->delete($table);
                } catch (ForeignKeyConstraintViolationException $ex) {
                    throw $ex;
                }
            });
        } else {
            foreach ($tables as $table) {
                $this->query->delete($table);
            }
        }
    }

    private function pretty($val)
    {
        return json_encode($val, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
