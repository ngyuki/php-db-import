<?php
namespace ngyuki\DbImport\DataSet;

class ArrayDataSet extends \ArrayIterator implements DataSetInterface
{
    public function __construct(array $array)
    {
        // おかしなデータが渡されたことを早期発見する
        foreach ($array as $table => $rows) {
            assert(is_string($table));
            assert(is_array($rows) || $rows instanceof \Traversable);
            foreach ($rows as $row) {
                assert(is_array($row) || $row instanceof \Traversable);
                foreach ($row as $column => $val) {
                    assert(is_string($column));
                    assert(!is_array($val));
                }
            }
        }

        parent::__construct($array);
    }


    public function getData()
    {
        return $this->getArrayCopy();
    }
}
