<?php
namespace ngyuki\DbImport;

/**
 * 例外のメッセージにファイル名や行番号を付けるためのラッパー
 */
class DataRow extends \ArrayIterator
{
    /**
     * @var string
     */
    private $location;

    public function __construct(array $array, $location)
    {
        parent::__construct($array);
        $this->location = $location;
    }

    public function getLocation()
    {
        return $this->location;
    }
}
