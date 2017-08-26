<?php
namespace ngyuki\DbImport\DataSet;

use ngyuki\DbImport\DataRow;
use Symfony\Component\Yaml\Yaml;

class YamlDataSet implements \IteratorAggregate
{
    private $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function getIterator()
    {
        $file = file_get_contents($this->file);
        $arr = Yaml::parse($file);
        $arr = $this->filterDot($arr);

        $res = [];

        foreach ($arr as $t => $rows) {
            foreach ($rows as $i => $row) {
                $res[$t][$i] = new DataRow($row, sprintf("%s [%s]", $this->file, $t));
            }
        }

        return new \ArrayIterator($res);
    }

    private function filterDot($arr)
    {
        $res = [];
        foreach ($arr as $key => $val) {
            if ($key[0] !== '.') {
                $res[$key] = $val;
            }
        }
        return $res;
    }
}
