<?php
namespace ngyuki\DbImport\DataSet;

use ngyuki\DbImport\DataRow;

class PhpFileDataSet implements \IteratorAggregate
{
    private $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function getIterator()
    {
        /** @noinspection PhpIncludeInspection */
        $arr = include $this->file;

        $res = [];

        foreach ($arr as $t => $rows) {
            foreach ($rows as $i => $row) {
                $res[$t][$i] = new DataRow($row, sprintf("%s [%s]", $this->file, $t));
            }
        }

        return new \ArrayIterator($res);
    }
}
