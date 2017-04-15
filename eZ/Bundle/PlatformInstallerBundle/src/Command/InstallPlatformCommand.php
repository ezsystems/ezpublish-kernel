<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformInstallerBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\ConnectionException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

class InstallPlatformCommand extends Command
{
    /** @var \Doctrine\DBAL\Connection */
    private $db;

    /** @var \Symfony\Component\Console\Output\OutputInterface */
    private $output;

    /** @var \Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface */
    private $cacheClearer;

    /** @var \Symfony\Component\Filesystem\Filesystem */
    private $filesystem;

    /** @var string */
    private $cacheDir;

    /** @var string */
    private $environment;

    /** @var \EzSystems\PlatformInstallerBundle\Installer\Installer[] */
    private $installers = array();

    const EXIT_DATABASE_NOT_FOUND_ERROR = 3;
    const EXIT_GENERAL_DATABASE_ERROR = 4;
    const EXIT_PARAMETERS_NOT_FOUND = 5;
    const EXIT_UNKNOWN_INSTALL_TYPE = 6;
    const EXIT_MISSING_PERMISSIONS = 7;

    public function __construct(
        Connection $db,
        array $installers,
        CacheClearerInterface $cacheClearer,
        Filesystem $filesystem,
        $cacheDir,
        $environment
    ) {
        $this->db = $db;
        $this->installers = $installers;
        $this->cacheClearer = $cacheClearer;
        $this->filesystem = $filesystem;
        $this->cacheDir = $cacheDir;
        $this->environment = $environment;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('ezplatform:install');
        $this->addArgument(
            'type',
            InputArgument::REQUIRED,
            'The type of install. Available options: ' . implode(', ', array_keys($this->installers))
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->checkPermissions();
        $this->checkParameters();
        $this->checkDatabase();

        $type = $input->getArgument('type');
        $installer = $this->getInstaller($type);
        if ($installer === false) {
            $output->writeln(
                "Unknown install type '$type', available options in currently installed eZ Platform package: " .
                implode(', ', array_keys($this->installers))
            );
            exit(self::EXIT_UNKNOWN_INSTALL_TYPE);
        }

        $installer->setOutput($output);

        $installer->importSchema();
        $installer->importData();
        $installer->importBinaries();
        $this->cacheClear($output);
        $this->indexData($output);
    }

    private function checkPermissions()
    {
        if (!is_writable('web') && !is_writable('web/var')) {
            $this->output->writeln('[web/ | web/var] is not writable');
            exit(self::EXIT_MISSING_PERMISSIONS);
        }
    }

    private function checkParameters()
    {
        $parametersFile = 'app/config/parameters.yml';
        if (!is_file($parametersFile)) {
            $this->output->writeln("Required configuration file '$parametersFile' not found");
            exit(self::EXIT_PARAMETERS_NOT_FOUND);
        }
    }

    /**
     * @throws \Exception if an unexpected database error occurs
     */
    private function configuredDatabaseExists()
    {
        try {
            $this->db->connect();
        } catch (ConnectionException $e) {
            // @todo 1049 is MySQL's code for "database doesn't exist", refactor
            if ($e->getPrevious()->getCode() == 1049) {
                return false;
            }
            throw $e;
        }

        return true;
    }

    private function checkDatabase()
    {
        try {
            if (!$this->configuredDatabaseExists()) {
                $this->output->writeln(
                    sprintf(
                        "The configured database '%s' does not exist",
                        $this->db->getDatabase()
                    )
                );
                exit(self::EXIT_DATABASE_NOT_FOUND_ERROR);
            }
        } catch (ConnectionException $e) {
            $this->output->writeln('An error occurred connecting to the database:');
            $this->output->writeln($e->getMessage());
            $this->output->writeln("Please check the database configuration in 'app/config/parameters.yml'");
            exit(self::EXIT_GENERAL_DATABASE_ERROR);
        }
    }

    private function cacheClear(OutputInterface $output)
    {
        if (!is_writable($this->cacheDir)) {
            throw new \RuntimeException(
                sprintf(
                    'Unable to write in the "%s" directory, check install doc on disk permissions before you continue.',
                    $this->cacheDir
                )
            );
        }

        $output->writeln(sprintf('Clearing cache for directory <info>%s</info>', $this->cacheDir));
        $oldCacheDir = $this->cacheDir . '_old';

        if ($this->filesystem->exists($oldCacheDir)) {
            $this->filesystem->remove($oldCacheDir);
        }

        $this->cacheClearer->clear($this->cacheDir);

        $this->filesystem->rename($this->cacheDir, $oldCacheDir);
        $this->filesystem->remove($oldCacheDir);
    }

    /**
     * Calls indexing commands.
     *
     * @todo This should not be needed once/if the Installer starts using API in the future.
     *       So temporary measure until it is not raw SQL based for the data itself (as opposed to the schema).
     *       This is done after cache clearing to make sure no cached data from before sql import is used.
     *
     * IMPORTANT: This is done using a command because config has change, so container and all services are different.
     *
     * @param OutputInterface $output
     */
    private function indexData(OutputInterface $output)
    {
        $output->writeln(
            sprintf('Search engine re-indexing, executing command ezplatform:reindex')
        );
        $this->executeCommand($output, 'ezplatform:reindex');
    }

    /**
     * @param $type
     *
     * @return \EzSystems\PlatformInstallerBundle\Installer\Installer
     */
    private function getInstaller($type)
    {
        if (!isset($this->installers[$type])) {
            return false;
        }

        return $this->installers[$type];
    }

    /**
     * Executes a Symfony command in separate process.
     *
     * Typically useful when configuration has changed, or you are outside of Symfony context (Composer commands).
     *
     * Based on {@see \Sensio\Bundle\DistributionBundle\Composer\ScriptHandler::executeCommand}.
     *
     * @param OutputInterface $output
     * @param string $cmd eZ Platform command to execute, like 'ezplatform:solr_create_index'
     *               Escape any user provided arguments, like: 'assets:install '.escapeshellarg($webDir)
     * @param int $timeout
     */
    private function executeCommand(OutputInterface $output, $cmd, $timeout = 300)
    {
        $phpFinder = new PhpExecutableFinder();
        if (!$phpPath = $phpFinder->find(false)) {
            throw new \RuntimeException('The php executable could not be found, add it to your PATH environment variable and try again');
        }

        // We don't know which php arguments where used so we gather some to be on the safe side
        $arguments = $phpFinder->findArguments();
        if (false !== ($ini = php_ini_loaded_file())) {
            $arguments[] = '--php-ini=' . $ini;
        }

        // Pass memory_limit in case this was specified as php argument, if not it will most likely be same as $ini.
        if ($memoryLimit = ini_get('memory_limit')) {
            $arguments[] = '-d memory_limit=' . $memoryLimit;
        }

        $phpArgs = implode(' ', array_map('escapeshellarg', $arguments));
        $php = escapeshellarg($phpPath) . ($phpArgs ? ' ' . $phpArgs : '');

        // Make sure to pass along relevant global Symfony options to console command
        $console = escapeshellarg('bin/console');
        if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
            $console .= ' -' . str_repeat('v', $output->getVerbosity() - 1);
        }

        if ($output->isDecorated()) {
            $console .= ' --ansi';
        }

        $console .= ' --env=' . escapeshellarg($this->environment);

        $process = new Process($php . ' ' . $console . ' ' . $cmd, null, null, null, $timeout);
        $process->run(function ($type, $buffer) use ($output) { $output->write($buffer, false); });
        if (!$process->isSuccessful()) {
            throw new \RuntimeException(sprintf('An error occurred when executing the "%s" command.', escapeshellarg($cmd)));
        }
    }
}
