<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\Command;

use eZ\Bundle\EzPublishCoreBundle\URLChecker\URLCheckerInterface;
use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\URLService;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\URL\Query\Criterion;
use eZ\Publish\API\Repository\Values\URL\Query\SortClause;
use eZ\Publish\API\Repository\Values\URL\URLQuery;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckURLsCommand extends Command
{
    private const DEFAULT_ITERATION_COUNT = 50;
    private const DEFAULT_REPOSITORY_USER = 'admin';

    /** @var \eZ\Publish\API\Repository\UserService */
    private $userService;

    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    private $permissionResolver;

    /** @var \eZ\Publish\API\Repository\URLService */
    private $urlService;

    /** @var \eZ\Bundle\EzPublishCoreBundle\URLChecker\URLCheckerInterface */
    private $urlChecker;

    public function __construct(
        UserService $userService,
        PermissionResolver $permissionResolver,
        URLService $urlService,
        URLCheckerInterface $urlChecker
    ) {
        parent::__construct('ezplatform:check-urls');

        $this->userService = $userService;
        $this->permissionResolver = $permissionResolver;
        $this->urlService = $urlService;
        $this->urlChecker = $urlChecker;
    }

    public function configure(): void
    {
        $this->setDescription('Checks validity of external URLs');

        $this->addOption(
            'iteration-count',
            'c',
            InputOption::VALUE_OPTIONAL,
            'Number of urls to be checked in a single iteration, for avoiding using too much memory',
            self::DEFAULT_ITERATION_COUNT
        );

        $this->addOption(
            'user',
            'u',
            InputOption::VALUE_OPTIONAL,
            'eZ Platform username (with Role containing at least Content policies: read, versionread, edit, remove, versionremove)',
            self::DEFAULT_REPOSITORY_USER
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->permissionResolver->setCurrentUserReference(
            $this->userService->loadUserByLogin($input->getOption('user'))
        );

        $limit = $input->getOption('iteration-count');
        if (!ctype_digit($limit) || (int)$limit < 1) {
            throw new RuntimeException("'--iteration-count' option should be > 0, got '{$limit}'");
        }

        $limit = (int)$limit;

        $query = new URLQuery();
        $query->filter = new Criterion\VisibleOnly();
        $query->sortClauses = [
            new SortClause\URL(),
        ];
        $query->offset = 0;
        $query->limit = $limit;

        $totalCount = $this->getTotalCount();

        $progress = new ProgressBar($output, $totalCount);
        $progress->start();
        while ($query->offset < $totalCount) {
            $this->urlChecker->check($query);

            $progress->advance(min($limit, $totalCount - $query->offset));
            $query->offset += $limit;
        }
        $progress->finish();
    }

    private function getTotalCount(): int
    {
        $query = new URLQuery();
        $query->filter = new Criterion\VisibleOnly();
        $query->limit = 0;

        return $this->urlService->findUrls($query)->totalCount;
    }
}
