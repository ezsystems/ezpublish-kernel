<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\Command;

use eZ\Publish\SPI\Persistence\TransactionHandler;
use eZ\Publish\SPI\Persistence\User\PasswordBlacklist\Handler as PasswordBlacklistHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use InvalidArgumentException;
use Throwable;

class ImportPasswordBlacklistCommand extends Command
{
    /** @var \eZ\Publish\SPI\Persistence\TransactionHandler */
    private $transactionHandler;

    /** @var \eZ\Publish\SPI\Persistence\User\PasswordBlacklist\Handler */
    private $blacklistHandler;

    /**
     * @param \eZ\Publish\SPI\Persistence\TransactionHandler $transactionHandler
     * @param \eZ\Publish\SPI\Persistence\User\PasswordBlacklist\Handler $blacklistHandler
     * @param string|null $name
     */
    public function __construct(
        TransactionHandler $transactionHandler,
        PasswordBlacklistHandler $blacklistHandler,
        ?string $name = null
    ) {
        parent::__construct($name);

        $this->transactionHandler = $transactionHandler;
        $this->blacklistHandler = $blacklistHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(): void
    {
        $this->setName('ezplatform:password:import-blacklist');
        $this->setDescription('Imports password blacklist');
        $this->addArgument('filepath', InputArgument::REQUIRED);
        $this->addOption('truncate', null, InputOption::VALUE_OPTIONAL, 'Truncates blacklist before import');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $passwords = $this->loadPasswordsFile($input->getArgument('filepath'));
        $passwords = array_map('trim', $passwords);
        $passwords = array_filter($passwords, 'mb_strlen');
        $passwords = array_unique($passwords);

        $this->transactionHandler->beginTransaction();
        try {
            if ($input->getOption('truncate') === 'true') {
                $this->blacklistHandler->removeAll();
            }

            $this->blacklistHandler->insert($passwords);
            $this->transactionHandler->commit();
        } catch (Throwable $e) {
            $this->transactionHandler->rollback();
        }

        $output->writeln('<info>Passwords has been imported.</info>');
    }

    /**
     * Loads list of password from given filepath.
     *
     * @param string $filepath
     *
     * @return string[]
     */
    private function loadPasswordsFile(string $filepath): array
    {
        if (!file_exists($filepath)) {
            throw new InvalidArgumentException("File $filepath not found.");
        }

        if (!is_readable($filepath)) {
            throw new InvalidArgumentException("File $filepath is not readable.");
        }

        return explode(PHP_EOL, file_get_contents($filepath));
    }
}
