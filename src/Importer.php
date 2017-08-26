<?php
namespace ngyuki\DbImport;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use ngyuki\DbImport\DataSet\PhpFileDataSet;
use ngyuki\DbImport\DataSet\YamlDataSet;
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

    public function __construct(Connection $connection, OutputInterface $output = null)
    {
        $this->conn = $connection;
        $this->output = $output ?? new NullOutput();
    }

    /**
     * @param bool $val
     * @return $this
     */
    public function setDelete($val)
    {
        $this->delete = $val;
        return $this;
    }

    /**
     * @param string|string[] $sql
     * @return $this
     */
    public function addBeforeSql($sql)
    {
        $this->before = array_merge($this->before, (array)$sql);
        return $this;
    }

    /**
     * @param string|string[] $sql
     * @return $this
     */
    public function addAfterSql($sql)
    {
        $this->after = array_merge($this->after, (array)$sql);
        return $this;
    }

    /**
     * @param string[] $files
     */
    public function importFiles(array $files)
    {
        $extensions = [
            'php' => function ($file) { return new PhpFileDataSet($file); },
            'yml' => function ($file) { return new YamlDataSet($file); },
            'yaml' => function ($file) { return new YamlDataSet($file); },
        ];

        $list = [];

        foreach ($files as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (!isset($extensions[$ext])) {
                throw new \RuntimeException(sprintf(
                    'Unknown extension file "%s"',
                    $file
                ));
            }
            $loader = $extensions[$ext];
            $list[] = $loader($file);
        }

        $this->importDataSet($list);
    }

    /**
     * @param \Traversable[] $list
     */
    public function importDataSet(array $list)
    {
        $tables = [];

        foreach ($list as $dataSet) {
            foreach ($dataSet as $table => $rows) {
                foreach ($rows as $row) {
                    $tables[$table][] = $row;
                }
            }
        }

        $this->import($tables);
    }

    private function import(array $tables)
    {
        try {
            foreach ($this->before as $sql) {
                if ($this->output->isDebug()) {
                    $this->output->writeln($sql);
                }
                $this->conn->exec($sql);
            }
            foreach ($tables as $table => $rows) {
                $this->importTable($table, $rows);
            }
            foreach ($this->after as $sql) {
                if ($this->output->isDebug()) {
                    $this->output->writeln($sql);
                }
                $this->conn->exec($sql);
            }
        } catch (DBALException $ex) {
            // DB エラーでは $previous を切る
            throw new \RuntimeException($ex->getMessage(), $ex->getCode());
        }
    }

    protected function importTable($table, $rows)
    {
        $tableExpr = $this->conn->quoteIdentifier($table);

        $logPrefix  = "<info>[$table]</info>";

        if ($this->delete) {
            $this->output->write("$logPrefix DELETE .");
            $num = $this->conn->createQueryBuilder()->delete($tableExpr)->execute();
            $this->output->writeln(".. $num rows done");
        }

        $this->progress("$logPrefix INSERT", count($rows), function () use ($rows, $tableExpr) {

            $num = 0;

            foreach ($rows as $row) {
                yield null => $row;
                $this->conn->insert($tableExpr, $row);
                yield '.' => null;
                $num++;
            }

            return "$num rows done";
        });
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
