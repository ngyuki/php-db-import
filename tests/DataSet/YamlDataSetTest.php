<?php
namespace ngyuki\DbImport\Test\DataSet;

use ngyuki\DbImport\Console\ConfigLoader;
use ngyuki\DbImport\Console\ConnectionManager;
use ngyuki\DbImport\DataSet\YamlDataSet;
use ngyuki\DbImport\Importer;
use PHPUnit\Framework\TestCase;

/**
 * @author ngyuki
 */
class YamlDataSetTest extends TestCase
{
    /**
     * @test
     */
    public function test_()
    {
        $config = (new ConfigLoader())->load(null);
        $connection = (new ConnectionManager())->getConnection($config);

        $data = new YamlDataSet(__DIR__ . "/_files/data.yml");
        $data = $data->getData(new Importer($connection));

        array_walk_recursive($data, function (&$val) {
            if ($val instanceof \Traversable) {
                $val = iterator_to_array($val);
            }
        });

        assertThat($data, equalTo([
            'aaa' => [
                [ 'id' => null,  'name' => 'ore' ],
                [ 'id' => null,  'name' => 'are' ],
                [ 'id' => 2001,  'name' => 'kore' ],
                [ 'id' => 2002,  'name' => 'ure' ],
            ],
            'bbb' => [
                [ 'id' => 1001, 'name' => 'sore' ],
                [ 'id' => 1002, 'name' => 'dore' ],
                [ 'id' =>  999, 'name' => 'xxx' ],
            ],
        ]));
    }
}
