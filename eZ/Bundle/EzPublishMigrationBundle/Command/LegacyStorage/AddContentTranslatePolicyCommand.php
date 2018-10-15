<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishMigrationBundle\Command\LegacyStorage;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Input\InputOption;
use eZ\Publish\Core\Repository\Values\User\PolicyCreateStruct;
use eZ\Publish\API\Repository\Values\User\Role;

class AddContentTranslatePolicyCommand extends ContainerAwareCommand
{
    /**
     * @var \eZ\Publish\API\Repository\RoleService
     */
    private $roleService;

    protected function configure()
    {
        $this
            ->setName('ezpublish:update:legacy_storage_add_content_translate_policy')
            ->addOption(
                'user',
                'u',
                InputOption::VALUE_OPTIONAL,
                'eZ Platform username (with Role containing at least Role policies: read, create, update)',
                'admin'
            )
            ->setDescription('Assign content/translate policy to role who has content/edit policy.')
            ->setHelp(
                <<<EOT
The command <info>%command.name%</info> assigns content/translate policy to role who has content/edit policy. See: https://jira.ez.no/browse/EZP-29223

<warning>During the script execution the database should not be modified.

To avoid surprises you are advised to create a backup or execute a dry run:
 
    %command.name% dry-run
    
before proceeding with actual update.</warning>

Since this script can potentially run for a very long time, to avoid memory
exhaustion run it in production environment using the <info>--env=prod</info> switch.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $roles = $this->filterRoles($this->roleService->loadRoles());
        $totalCount = count($roles);
        $output->writeln('Found total roles that have "content/edit" policy: ' . $totalCount);

        if ($totalCount === 0) {
            $output->writeln('Nothing to process, exiting.');

            return;
        }

        $rolesIdentifiers = implode(',', array_map(function ($role) {
            return $role->identifier;
        }, $roles));

        $output->writeln('Roles with "content/edit" policy are: ' . $rolesIdentifiers);

        if ($this->confirmExecution($input, $output)) {
            foreach ($roles as $role) {
                $this->addContentTranslatePolicyToRole($role);

                $output->writeln('Added policy "content/translate" to role ' . $role->identifier);
            }
        }

        $output->writeln('');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        /** @var \eZ\Publish\API\Repository\Repository $repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');
        $this->roleService = $repository->getRoleService();

        $repository->getPermissionResolver()->setCurrentUserReference(
            $repository->getUserService()->loadUserByLogin($input->getOption('user'))
        );
    }

    protected function confirmExecution(InputInterface $input, OutputInterface $output)
    {
        $question = new ConfirmationQuestion('<question>Are you sure you want to proceed?</question> ', false);

        return $this
            ->getHelper('question')
            ->ask($input, $output, $question);
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\User\Role[]
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role[]
     */
    private function filterRoles(array $roles): array
    {
        $rolesWithContentEdit = [];

        /** @var \eZ\Publish\API\Repository\Values\User\Role $role */
        foreach ($roles as $role) {
            $hasContentEdit = false;
            $hasContentTranslate = false;

            foreach ($role->getPolicies() as $policy) {
                if ($policy->module === 'content' && $policy->function === 'edit') {
                    $hasContentEdit = true;
                }

                if ($policy->module === 'content' && $policy->function === 'translate') {
                    $hasContentTranslate = true;
                }
            }

            if ($hasContentEdit && !$hasContentTranslate) {
                $rolesWithContentEdit[] = $role;
            }
        }

        return $rolesWithContentEdit;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\User\Role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function addContentTranslatePolicyToRole(Role $role): void
    {
        $policyCreateStruct = $this->getPolicyCreateStruct();
        $roleDraft = $this->roleService->createRoleDraft($role);
        $roleDraft = $this->roleService->addPolicyByRoleDraft($roleDraft, $policyCreateStruct);
        $this->roleService->publishRoleDraft($roleDraft);
    }

    /**
     * @return \eZ\Publish\Core\Repository\Values\User\PolicyCreateStruct
     */
    private function getPolicyCreateStruct(): PolicyCreateStruct
    {
        $policyCreateStruct = new PolicyCreateStruct([
            'module' => 'content',
            'function' => 'translate',
        ]);

        return $policyCreateStruct;
    }
}
