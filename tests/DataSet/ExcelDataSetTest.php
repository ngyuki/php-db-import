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
        $example = __DIR__ . '/../../example';
        $data = new ExcelDataSet("$example/files/004.xlsx");

        $arr = iterator_to_array($data);
        assertThat($arr, equalTo([
            'xxx' => [
                ['id' => 1, 'name' => 'aaa'],
                ['id' => 2, 'name' => 'bbb'],
                ['id' => 3, 'name' => 'ccc'],
            ],
        ]));
    }
}
