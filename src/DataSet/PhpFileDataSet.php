<?php
namespace ngyuki\DbImport\DataSet;

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
        return new \ArrayIterator($arr);
    }
}
