<?php
namespace ngyuki\DbImport\DataSet;

use ngyuki\DbImport\DataRow;
use PHPExcel_Reader_Excel2007;
use PHPExcel_Shared_Date;

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

            $columns = $this->parseColumns(array_shift($arr));

            foreach ($arr as $line => $row) {
                $assoc = $this->applyColumns($columns, $row);

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

    private function parseColumns(array $columns)
    {
        $results = [];

        foreach ($columns as $i => $column) {
            $modifiers = explode('|', $column);
            $column = trim(array_shift($modifiers));
            if (strlen($column)) {
                $results[$i] = [$column, $modifiers];
            }
        }

        return $results;
    }

    private function applyColumns($columns, $row)
    {
        $assoc = [];

        foreach ($columns as $i => list ($column, $modifiers)) {
            $val = $row[$i] ?? null;
            foreach ($modifiers as $modifier) {
                $method = 'modifier_' . $modifier;
                if (method_exists($this, $method) === false) {
                    throw new \LogicException("Excel modified $modifier not found");
                }
                $val = $this->$method($val);
            }
            $assoc[$column] = $val;
        }

        return $assoc;
    }

    protected function modifier_date($val)
    {
        if ($val !== null) {
            return gmdate('Y/m/d H:i:s', PHPExcel_Shared_Date::ExcelToPHP($val));
        }
        return $val;
    }

    protected function modifier_time($val)
    {
        if ($val !== null) {
            return gmdate('H:i:s', PHPExcel_Shared_Date::ExcelToPHP($val));
        }
        return $val;
    }
}
