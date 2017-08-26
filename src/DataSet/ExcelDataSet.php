<?php
namespace ngyuki\DbImport\DataSet;

use ngyuki\DbImport\DataRow;
use ngyuki\DbImport\EmptyValue;
use PHPExcel_Reader_Excel2007;

class ExcelDataSet implements \IteratorAggregate
{
    private $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function getIterator()
    {
        return (function () {
            $reader = new PHPExcel_Reader_Excel2007();
            $reader->setIncludeCharts(false);
            $reader->setReadDataOnly(true);
            $excel = $reader->load($this->file);

            foreach ($excel->getSheetNames() as $name) {
                $arr = $excel->getSheetByName($name)->toArray();
                yield $name => $this->processArray($name, $arr);
            }
        })();
    }

    private function processArray($name, $arr)
    {
        $columns = array_shift($arr);

        $data = [];

        foreach ($arr as $line => $row) {
            $assoc = array_combine($columns, $row);
            if (!array_filter($assoc, function ($v) {
                return strlen($v);
            })) {
                continue;
            }
            $assoc = array_map(
                function ($v) {
                    if (strlen($v) === 0) {
                        return EmptyValue::val();
                    }
                    return $v;
                },
                $assoc
            );
            $data[] = new DataRow($assoc, sprintf("%s [%s] (%d)", $this->file, $name, $line + 1));
        }

        return $data;
    }
}
