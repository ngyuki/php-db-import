<?php
namespace ngyuki\DbImport\Test\DataSet;

use ngyuki\DbImport\DataSet\ExcelDataSet;
use ngyuki\DbImport\EmptyValue;
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

        array_walk_recursive($data, function (&$val) {
            if ($val instanceof EmptyValue) {
                $val = null;
            }
        });

        assertThat($data, equalTo([
            'xxx' => [
                ['id' => 1, 'no' => 1000, 'name' => 'aa', 'memo' => 'aaa'],
                ['id' => 2, 'no' => null, 'name' => 'bb', 'memo' => null],
                ['id' => 3, 'no' => 3000, 'name' => null, 'memo' => 'ccc'],
            ],
        ]));
    }
}
