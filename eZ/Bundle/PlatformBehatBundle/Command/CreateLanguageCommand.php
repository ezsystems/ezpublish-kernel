<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformBehatBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateLanguageCommand extends ContainerAwareCommand
{
    /** @var \eZ\Publish\API\Repository\LanguageService */
    private $languageService;

    /** @var \eZ\Publish\API\Repository\UserService */
    private $userService;

    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    private $permissionResolver;

    protected function configure()
    {
        $this
            ->setName('ez:behat:create-language')
            ->setDescription('Create a Language')
            ->addArgument('language-code', InputArgument::REQUIRED)
            ->addArgument('language-name', InputArgument::OPTIONAL, 'Language name', '')
            ->addArgument(
                'user',
                InputArgument::OPTIONAL,
                'eZ Platform User with access to content / translations',
                'admin'
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $repository = $this->getContainer()->get('ezpublish.api.repository');
        $this->languageService = $repository->getContentLanguageService();
        $this->permissionResolver = $repository->getPermissionResolver();
        $this->userService = $repository->getUserService();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // set user with proper permissions to create language (content / translations)
        $this->permissionResolver->setCurrentUserReference(
            $this->userService->loadUserByLogin(
                $input->getArgument('user')
            )
        );

        $languageCreateStruct = $this->languageService->newLanguageCreateStruct();
        $languageCreateStruct->languageCode = $input->getArgument('language-code');
        $languageCreateStruct->name = $input->getArgument('language-name');

        $this->languageService->createLanguage($languageCreateStruct);
    }
}
