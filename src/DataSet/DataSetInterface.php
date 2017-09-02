<?php
namespace ngyuki\DbImport\DataSet;

use ngyuki\DbImport\Importer;

interface DataSetInterface
{
    /**
     * データセットのデータを返す
     *
     * 次のような構造の三次元連想配列
     * 配列ではなく Traversable なオブジェクトでも良い
     *
     * <code>
     *  [
     *      'table' => [
     *          [ 'id' => 1, 'name' => 'aaa' ],
     *          [ 'id' => 2, 'name' => 'bbb' ],
     *      ],
     *      'table' => [
     *          [ 'id' => 1, 'name' => 'aaa' ],
     *          [ 'id' => 2, 'name' => 'bbb' ],
     *      ],
     *  ]
     * </code>
     *
     * @param Importer $importer
     * @return array
     */
    public function getData(Importer $importer);
}
