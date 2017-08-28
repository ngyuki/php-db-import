<?php
namespace ngyuki\DbImport\Test\DataSet;

use ngyuki\DbImport\DataSet\ExcelDataSet;
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
        $example = __DIR__ . '/../../example';
        $data = new ExcelDataSet("$example/files/004.xlsx");
        $data = $data->getData();

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
                ],
                [
                    'id' => 2,
                    'no' => null,
                    'name' => 'bb',
                    'memo' => null,
                    'date' => '9999/12/31 00:00:00',
                    'time' => '23:59:59'
                ],
                [
                    'id' => null,
                    'no' => null,
                    'name' => '',
                    'memo' => 'ccc',
                    'date' => null,
                    'time' => null
                ],
            ],
        ]));
    }
}
