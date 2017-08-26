<?php
namespace ngyuki\DbImport\Test\DataSet;

use ngyuki\DbImport\Console\ConfigLoader;
use ngyuki\DbImport\Console\ConnectionManager;
use ngyuki\DbImport\DataSet\ExcelDataSet;
use ngyuki\DbImport\EmptyValue;
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
        $example = __DIR__ . '/../../example';
        $data = new ExcelDataSet("$example/files/004.xlsx");

        $arr = iterator_to_array($data);

        $emp = EmptyValue::val();

        assertThat($arr, equalTo([
            'xxx' => [
                ['id' => 1, 'no' => 1000, 'name' => 'aa', 'memo' => 'aaa'],
                ['id' => 2, 'no' => $emp, 'name' => 'bb', 'memo' => $emp],
                ['id' => 3, 'no' => 3000, 'name' => $emp, 'memo' => 'ccc'],
            ],
        ]));
    }
}
