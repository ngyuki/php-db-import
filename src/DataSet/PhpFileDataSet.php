<?php
namespace ngyuki\DbImport\DataSet;

use ngyuki\DbImport\DataRow;

class PhpFileDataSet implements DataSetInterface
{
    private $file;

    public function __construct($file)
    {
        $this->file = realpath($file);

        if ($this->file === false) {
            throw new \RuntimeException("File not found ... $file");
        }

        if (is_readable($this->file) === false) {
            throw new \RuntimeException("File not readable ... $this->file");
        }
    }

    public function getData()
    {
        /** @noinspection PhpIncludeInspection */
        $arr = include $this->file;

        if ($arr === false) {
            throw new \RuntimeException("File read failed ... $this->file");
        }

        $tables = [];

        foreach ($arr as $table => $rows) {
            foreach ($rows as $row) {
                $tables[$table][] = new DataRow($row, sprintf("%s [%s]", $this->file, $table));
            }
        }

        return $tables;
    }
}
