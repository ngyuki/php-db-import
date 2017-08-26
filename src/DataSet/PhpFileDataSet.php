<?php
namespace ngyuki\DbImport\DataSet;

use ngyuki\DbImport\DataRow;

class PhpFileDataSet implements \IteratorAggregate
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

    public function getIterator()
    {
        /** @noinspection PhpIncludeInspection */
        $arr = include $this->file;

        if ($arr === false) {
            throw new \RuntimeException("File read failed ... $this->file");
        }

        $res = [];

        foreach ($arr as $t => $rows) {
            foreach ($rows as $i => $row) {
                $res[$t][$i] = new DataRow($row, sprintf("%s [%s]", $this->file, $t));
            }
        }

        return new \ArrayIterator($res);
    }
}
