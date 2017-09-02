<?php
namespace ngyuki\DbImport;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use ngyuki\DbImport\DataSet\DataSetInterface;
use ngyuki\DbImport\DataSet\ExcelDataSet;
use ngyuki\DbImport\DataSet\PhpFileDataSet;
use ngyuki\DbImport\DataSet\YamlDataSet;
use ngyuki\DbImport\Exception\DatabaseException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @var OutputInterface
     */
    private $output;

    /**
     * @var bool
     */
    private $delete;

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

    public function __construct(Connection $connection, OutputInterface $output = null)
    {
        $this->conn = $connection;
        $this->query = new Query($connection);
        $this->output = $output ?? new NullOutput();
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
     * @return static
     */
    public function useDelete($val)
    {
        $obj = clone $this;
        $obj->delete = $val;
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
                throw new \LogicException(sprintf(
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
                if ($this->output->isDebug()) {
                    $this->output->writeln($sql);
                }
                $this->query->exec($sql);
            }
            if ($this->delete) {
                $this->down($tables);
            }
            $this->up($tables);
            foreach ($this->after as $sql) {
                if ($this->output->isDebug()) {
                    $this->output->writeln($sql);
                }
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
            $this->progress(
                "<info>[$table]</info> INSERT",
                count($rows),
                function () use ($rows, $table) {
                    $num = 0;
                    foreach ($rows as $row) {
                        yield null => $row;
                        try {
                            $this->query->insert($table, $row);
                        } catch (\Throwable $ex) {
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
                        yield '.' => null;
                        $num++;
                    }
                    return "$num rows done";
                }
            );
        }
    }

    private function down(array $tables)
    {
        $tables = array_reverse(array_keys($tables));

        foreach ($tables as $table) {
            $this->output->write("<info>[$table]</info> DELETE .");
            $num = $this->query->delete($table);
            $this->output->writeln(".. $num rows done");
        }
    }

    private function progress($start, $total, callable $callback)
    {
        /* @var $progress ProgressBar */
        $progress = null;

        if ($this->output->isDebug()) {
            $this->output->writeln("$start ...");
        } elseif ($this->output->isVeryVerbose()) {
            $this->output->writeln("$start ...");
            $progress = new ProgressBar($this->output, $total);
            $progress->start();
        } elseif ($this->output->isVerbose()) {
            $this->output->write("$start ");
        } else {
            $this->output->write("$start .");
        }

        /* @var $g \Generator */
        $g = $callback();

        foreach ($g as $key => $val) {
            if ($key === null) {
                if ($this->output->isDebug()) {
                    $this->output->writeln("  " . $this->pretty($val));
                }
            } else {
                if ($this->output->isDebug()) {
                    // none
                } elseif ($this->output->isVeryVerbose()) {
                    $progress->advance();
                } elseif ($this->output->isVerbose()) {
                    $this->output->write($key);
                }
            }
        }

        $end = $g->getReturn();

        if ($this->output->isDebug()) {
            $this->output->writeln("    ... $end");
        } elseif ($this->output->isVeryVerbose()) {
            $progress->finish();
            $this->output->writeln(" ... $end");
        } elseif ($this->output->isVerbose()) {
            $this->output->writeln(" $end");
        } else {
            $this->output->writeln(".. $end");
        }
    }

    private function pretty($val)
    {
        return json_encode($val, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
