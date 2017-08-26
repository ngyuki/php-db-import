<?php
namespace ngyuki\DbImport\DataSet;

class ArrayDataSet extends \ArrayIterator implements \Traversable
{
    public function __construct(array $array)
    {
        parent::__construct($array);
    }
}
