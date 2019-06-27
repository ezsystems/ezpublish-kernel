<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformInstallerBundle\Installer;

use Doctrine\DBAL\Connection;
use Symfony\Component\Filesystem\Filesystem;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

class DbBasedInstaller
{
    /** @var \Doctrine\DBAL\Connection */
    protected $db;

    /** @var \Symfony\Component\Console\Output\OutputInterface */
    protected $output;

    /** @var string */
    protected $baseDataDir;

    public function __construct(Connection $db)
    {
        $this->db = $db;
        // parametrized so other installer implementations can override this
        $this->baseDataDir = __DIR__ . '/../../../../../data';
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * Copy and override configuration file.
     *
     * @param string $source
     * @param string $target
     */
    protected function copyConfigurationFile($source, $target)
    {
        $fs = new Filesystem();
        $fs->copy($source, $target, true);

        if (!$this->output->isQuiet()) {
            $this->output->writeln("Copied $source to $target");
        }
    }

    protected function runQueriesFromFile($file)
    {
        $queries = array_filter(preg_split('(;\\s*$)m', file_get_contents($file)));

        if (!$this->output->isQuiet()) {
            $this->output->writeln(
                sprintf(
                    '<info>Executing %d queries from <comment>%s</comment> on database <comment>%s</comment></info>',
                    count($queries),
                    $file,
                    $this->db->getDatabase()
                )
            );
        }

        foreach ($queries as $query) {
            $this->db->exec($query);
        }
    }

    /**
     * Get DBMS-specific SQL data file path.
     *
     * @param string $relativeFilePath SQL file path relative to {baseDir}/{dbms} directory
     *
     * @return string absolute existing file path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @since 6.13
     */
    final protected function getKernelSQLFileForDBMS($relativeFilePath)
    {
        $databasePlatform = $this->db->getDatabasePlatform()->getName();
        $filePath = "{$this->baseDataDir}/{$databasePlatform}/{$relativeFilePath}";

        if (!is_readable($filePath)) {
            throw new InvalidArgumentException(
                '$relativeFilePath',
                sprintf(
                    'DBMS-specific file for %s database platform does not exist or is not readable: %s',
                    $databasePlatform,
                    $filePath
                )
            );
        }

        // apply realpath for more user-friendly Console output
        return realpath($filePath);
    }
}
