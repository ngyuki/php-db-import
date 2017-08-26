<?php
namespace ngyuki\DbImport\DataSet;

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
            if (array_filter($assoc, function ($v) { return strlen($v); })) {
                $data[] = array_combine($columns, $row);
            }
        }

        return $data;
    }
}





