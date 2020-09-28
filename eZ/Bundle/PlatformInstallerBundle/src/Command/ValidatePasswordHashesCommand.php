<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformInstallerBundle\Command;

use eZ\Publish\Core\FieldType\User\UserStorage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ValidatePasswordHashesCommand extends Command
{
    /** @var \eZ\Publish\Core\FieldType\User\UserStorage */
    private $userStorage;

    public function __construct(
        UserStorage $userStorage
    ) {
        $this->userStorage = $userStorage;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('ezplatform:user:validate-password-hashes');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $unsupportedHashesCounter = $this->userStorage->countUsersWithUnsupportedHashType();

        if ($unsupportedHashesCounter > 0) {
            $output->writeln(sprintf('<error>Found %s users with unsupported password hash types</error>', $unsupportedHashesCounter));
            $output->writeln('<info>For more details check documentation:</info> <href=https://doc.ezplatform.com/en/latest/releases/ez_platform_v3.0_deprecations/#password-hashes>https://doc.ezplatform.com/en/latest/releases/ez_platform_v3.0_deprecations/#password-hashes</>');
        } else {
            $output->writeln('OK - <info>All users have supported password hash types</info>');
        }

        return Command::SUCCESS;
    }
}
