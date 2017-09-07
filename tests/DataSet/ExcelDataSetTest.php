<?php
namespace ngyuki\DbImport\Test\DataSet;

use ngyuki\DbImport\Console\ConfigLoader;
use ngyuki\DbImport\Console\ConnectionManager;
use ngyuki\DbImport\DataSet\ExcelDataSet;
use ngyuki\DbImport\Importer;
use PHPUnit\Framework\TestCase;

/**
 * @author ngyuki
 */
class ExcelDataSetTest extends TestCase
{
    /**
     * @test
     */
    public function test_()
    {
        $config = (new ConfigLoader())->load(null);
        $connection = (new ConnectionManager())->getConnection($config);

        $example = __DIR__ . '/../../example';
        $data = new ExcelDataSet("$example/files/004.xlsx");
        $data = $data->getData(new Importer($connection));

        array_walk_recursive($data, function (&$val) {
            if ($val instanceof \Traversable) {
                $val = iterator_to_array($val);
            }
        });

        assertThat($data, equalTo([
            'xxx' => [
                [
                    'id' => 1,
                    'no' => 1000,
                    'name' => 'aa',
                    'memo' => 'aaa',
                    'date' => '1999/01/01 00:00:00',
                    'time' => '12:13:14',
                    'datetime' => '1999/01/01 00:00:00',
                ],
                [
                    'id' => 2,
                    'no' => null,
                    'name' => 'bb',
                    'memo' => null,
                    'date' => '9999/12/31 00:00:00',
                    'time' => '23:59:59',
                    'datetime' => '9999/12/31 23:59:59',
                ],
                [
                    'id' => null,
                    'no' => null,
                    'name' => '',
                    'memo' => 'ccc',
                    'date' => null,
                    'time' => null,
                    'datetime' => null,
                ],
                [
                    'id' => 11,
                    'no' => 1000,
                    'name' => 'aa',
                    'memo' => 'aaa',
                    'date' => '1999/01/01 00:00:00',
                    'time' => '12:13:14',
                    'datetime' => '1999/01/01 00:00:00',
                ],
                [
                    'id' => 12,
                    'no' => null,
                    'name' => 'bb',
                    'memo' => null,
                    'date' => '9999/12/31 00:00:00',
                    'time' => '23:59:59',
                    'datetime' => '9999/12/31 23:59:59',
                ],
                [
                    'id' => null,
                    'no' => null,
                    'name' => '',
                    'memo' => 'ccc',
                    'date' => null,
                    'time' => null,
                    'datetime' => null,
                ],
            ],
        ]));
    }
}
