<?php
namespace ngyuki\DbImport\DataSet;

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
                $arr = $this->processArray($arr);
                yield $name => $arr;
            }
        })();
    }

    private function processArray($arr)
    {
        $columns = array_shift($arr);

        $data = [];

        foreach ($arr as $row) {
            $assoc = array_combine($columns, $row);
            if (!array_filter($assoc, function ($v) {
                return strlen($v);
            })) {
                continue;
            }
            $data[] = array_map(
                function ($v) {
                    if (strlen($v) === 0) {
                        return EmptyValue::val();
                    }
                    return $v;
                },
                $assoc
            );
        }

        return $data;
    }
}
