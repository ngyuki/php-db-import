<?php
namespace ngyuki\DbImport\DataSet;

use ngyuki\DbImport\Importer;
use Symfony\Component\Yaml\Parser;

class YamlDataSet implements DataSetInterface
{
    use DataSetUtil;

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

    public function getData(Importer $importer)
    {
        $file = file_get_contents($this->file);

        if ($file === false) {
            throw new \RuntimeException("File read failed ... $this->file");
        }

        $arr = (new Parser())->parse($file);

        if ($arr === false) {
            throw new \RuntimeException("Unable parse file ... $this->file");
        }

        return self::arrayToTables($this->file, $arr);
    }
}
