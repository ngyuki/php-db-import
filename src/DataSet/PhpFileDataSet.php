<?php
namespace ngyuki\DbImport\DataSet;

use ngyuki\DbImport\Exception\IOException;
use ngyuki\DbImport\Importer;

class PhpFileDataSet implements DataSetInterface
{
    use DataSetUtil;

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

        return self::arrayToTables($this->file, $arr);
    }
}
