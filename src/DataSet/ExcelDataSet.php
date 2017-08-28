<?php
namespace ngyuki\DbImport\DataSet;

use ngyuki\DbImport\DataRow;
use PHPExcel_Reader_Excel2007;

class ExcelDataSet implements DataSetInterface
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
        if (preg_match('/\~\$/', basename($this->file))) {
            // excel の一時ファイルは除外
            return [];
        }

        $reader = new PHPExcel_Reader_Excel2007();
        $reader->setIncludeCharts(false);
        $reader->setReadDataOnly(true);
        $excel = $reader->load($this->file);

        $tables = [];

        foreach ($excel->getSheetNames() as $name) {
            $arr = $excel->getSheetByName($name)->toArray();

            $columns = array_shift($arr);
            foreach ($arr as $line => $row) {
                $assoc = array_combine($columns, $row);

                // すべての列が空の行はスキップ
                if (!array_filter($assoc, function ($v) {
                    return strlen($v);
                })) {
                    continue;
                }

                $tables[$name][] = new DataRow($assoc, sprintf("%s [%s] (%d)", $this->file, $name, $line + 1));
            }
        }

        return $tables;
    }
}
