<?php
namespace ngyuki\DbImport\Console;

use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    const NAME = 'db-import';
    const VERSION = '@dev';

    public function __construct()
    {
        parent::__construct(self::NAME, self::VERSION);

        $commands = array();
        $commands[] = new Command\ImportCommand();

        $this->addCommands($commands);
    }
}
