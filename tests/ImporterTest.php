<?php
namespace ngyuki\DbImport\Test;

use ngyuki\DbImport\Console\ConfigLoader;
use ngyuki\DbImport\Console\ConnectionManager;
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
        // example に置いた設定ファイルを使うため ... 実際に直接使うときはこんなことしない
        $example = __DIR__ . '/../example';
        $config = (new ConfigLoader())->load($example);
        $connection = (new ConnectionManager())->getConnection($config);

        (new Importer($connection))
            ->useDelete(true)
            ->addFiles(glob("$example/files/*"))
            ->import();

        assertTrue(true);
    }
}
