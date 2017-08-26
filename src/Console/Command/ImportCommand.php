<?php
namespace ngyuki\DbImport\Console\Command;

use ngyuki\DbImport\Console\ConfigLoader;
use ngyuki\DbImport\Console\ConnectionManager;
use ngyuki\DbImport\Importer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends Command
{
    protected function configure()
    {
        parent::configure();

        $this->setName('import')->setDescription('Execute scripts')
            ->addArgument('files', InputArgument::REQUIRED|InputArgument::IS_ARRAY, 'Import files.')
            ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Config file or directory.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        // @see http://qiita.com/ngyuki/items/d8db4ab6a954c59ed79d
        if ($output->getVerbosity() == $output::VERBOSITY_NORMAL && $input->getOption('verbose')) {
            $output->setVerbosity($output::VERBOSITY_VERBOSE);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getOption('config');
        $config = (new ConfigLoader())->load($path);
        $connection = (new ConnectionManager())->getConnection($config);

        $importer = new Importer($connection, $output);

        $files = $input->getArgument('files');
        $importer->importFiles($files);
    }
}
