<?php
namespace ngyuki\DbImport\DataSet;

use ngyuki\DbImport\DataRow;
use ngyuki\DbImport\Importer;
use Symfony\Component\Yaml\Yaml;

class YamlDataSet implements DataSetInterface
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

    public function getData(Importer $importer)
    {
        $file = file_get_contents($this->file);

        if ($file === false) {
            throw new \RuntimeException("File read failed ... $this->file");
        }

        $arr = Yaml::parse($file);

        if ($arr === false) {
            throw new \RuntimeException("Unable parse file ... $this->file");
        }

        $tables = [];

        foreach ($arr as $table => $rows) {
            // ドットから始まるテーブル名は除外
            if ($table[0] === '.') {
                continue;
            }
            foreach ($rows as $row) {
                $tables[$table][] = new DataRow($row, sprintf("%s [%s]", $this->file, $table));
            }
        }

        return $tables;
    }
}
