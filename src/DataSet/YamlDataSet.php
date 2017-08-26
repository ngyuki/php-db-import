<?php
namespace ngyuki\DbImport\DataSet;

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
        return new \ArrayIterator($arr);
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
