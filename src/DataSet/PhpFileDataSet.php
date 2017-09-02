<?php
namespace ngyuki\DbImport\DataSet;

use ngyuki\DbImport\DataRow;
use ngyuki\DbImport\Exception\IOException;
use ngyuki\DbImport\Importer;

class PhpFileDataSet implements DataSetInterface
{
    private $file;

    public function __construct($file)
    {
        $this->file = realpath($file);

        if ($this->file === false) {
            throw new IOException("File not found ... $file");
        }

        if (is_readable($this->file) === false) {
            throw new IOException("File not readable ... $this->file");
        }
    }

    public function getData(Importer $importer)
    {
        /** @noinspection PhpIncludeInspection */
        $arr = include $this->file;

        if ($arr === false) {
            throw new IOException("File read failed ... $this->file");
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
