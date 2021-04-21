<?php
namespace ngyuki\DbImport\Test;

use ngyuki\DbImport\Console\ConnectionManager;
use ngyuki\DbImport\DataSet\ArrayDataSet;
use ngyuki\DbImport\Importer;
use PHPUnit\Framework\TestCase;

/**
 * @author ngyuki
 */
class ImporterTest extends TestCase
{
    /**
     * @test
     */
    public function test_()
    {
        $config = require __DIR__ . '/../example/config.php';
        $connection = (new ConnectionManager())->getConnection($config);

        $example = __DIR__ . '/../example';

        (new Importer($connection))
            ->useDelete(true)
            ->addFiles(glob("$example/files/*"))
            ->import();

        assertTrue(true);
    }

    /**
     * @test
     */
    public function ArrayDataSet()
    {
        $config = require __DIR__ . '/../example/config.php';
        $connection = (new ConnectionManager())->getConnection($config);

        (new Importer($connection))
            ->useDelete(true)
            ->addDataSet(new ArrayDataSet([
                'aaa' => [[
                    'id' => 1,
                    'name' => 'abc',
                ]],
            ]))
            ->import();

        $rows = $connection->fetchAll('select * from aaa');
        assertNotEmpty($rows);
    }

    /**
     * @test
     */
    public function empty_()
    {
        $config = require __DIR__ . '/../example/config.php';
        $connection = (new ConnectionManager())->getConnection($config);

        (new Importer($connection))
            ->useDelete(true)
            ->addDataSet(new ArrayDataSet([
                'aaa' => [],
            ]))
            ->import();

        $rows = $connection->fetchAll('select * from aaa');
        assertEmpty($rows);
    }
}
