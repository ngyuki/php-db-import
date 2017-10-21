<?php
namespace ngyuki\DbImport\Test\DataSet;

use ngyuki\DbImport\Console\ConfigLoader;
use ngyuki\DbImport\Console\ConnectionManager;
use ngyuki\DbImport\DataSet\ArrayDataSet;
use ngyuki\DbImport\Importer;
use PHPUnit\Framework\TestCase;

/**
 * @author ngyuki
 */
class ArrayDataSetTest extends TestCase
{
    /**
     * @test
     */
    public function test_()
    {
        $config = (new ConfigLoader())->load(null);
        $connection = (new ConnectionManager())->getConnection($config);

        $data = new ArrayDataSet([
            'aaa' => [
                [ 'id' => 100,  'name' => 'ore' ],
                [ 'id' => 101,  'name' => 'are' ],
            ],
            '^aaa' => [
                [ 'id', 'name' ],
                [ 200, 'sore' ],
                [ 201, 'dore' ],
            ],
            '@bbb' => [
                [ 'id', 'name' ],
                [ 300, 'kore' ],
                [ 301, 'more' ],
            ],
            'ccc' => [
                [ 'id' => 400,  'name' => 'aaa' ],
                [ 'id' => 401,  'name' => 'bbb' ],
            ],
            'ccc.x' => [
                [ 'id' => 500,  'name' => 'ccc' ],
                [ 'id' => 501,  'name' => 'ddd' ],
            ],
        ]);

        $data = $data->getData(new Importer($connection));

        array_walk_recursive($data, function (&$val) {
            if ($val instanceof \Traversable) {
                $val = iterator_to_array($val);
            }
        });

        assertThat($data, equalTo([
            'aaa' => [
                [ 'id' => 100, 'name' => 'ore' ],
                [ 'id' => 101, 'name' => 'are' ],
                [ 'id' => 200, 'name' => 'sore' ],
                [ 'id' => 201, 'name' => 'dore' ],
            ],
            'bbb' => [
                [ 'id' => 300, 'name' => 'kore' ],
                [ 'id' => 301, 'name' => 'more' ],
            ],
            'ccc' => [
                [ 'id' => 400, 'name' => 'aaa' ],
                [ 'id' => 401, 'name' => 'bbb' ],
                [ 'id' => 500, 'name' => 'ccc' ],
                [ 'id' => 501, 'name' => 'ddd' ],
            ],
        ]));
    }
}
