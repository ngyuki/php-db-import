<?php
namespace ngyuki\DbImport\DataSet;

use ngyuki\DbImport\DataRow;
use ngyuki\DbImport\EmptyValue;
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

                // 空文字を EmptyValue に変換
                $assoc = array_map(
                    function ($v) {
                        if (strlen($v) === 0) {
                            return EmptyValue::val();
                        }
                        return $v;
                    },
                    $assoc
                );

                $tables[$name][] = new DataRow($assoc, sprintf("%s [%s] (%d)", $this->file, $name, $line + 1));
            }
        }

        return $tables;
    }
}
