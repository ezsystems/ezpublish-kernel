<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Command;

use eZ\Publish\API\Repository\URLService;
use eZ\Publish\API\Repository\Values\URL\Query\Criterion;
use eZ\Publish\API\Repository\Values\URL\Query\SortClause;
use eZ\Publish\API\Repository\Values\URL\URLQuery;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckURLsCommand extends ContainerAwareCommand
{
    const DEFAULT_ITERATION_COUNT = 50;
    const DEFAULT_REPOSITORY_USER = 'admin';

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('ezplatform:check-urls');
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

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repository = $this->getContainer()->get('ezpublish.api.repository');
        $repository->getPermissionResolver()->setCurrentUserReference(
            $repository->getUserService()->loadUserByLogin($input->getOption('user'))
        );

        $limit = $input->getOption('iteration-count');
        if (!is_numeric($limit) || (int)$limit < 1) {
            throw new RuntimeException("'--iteration-count' option should be > 0, got '{$limit}'");
        }

        $query = new URLQuery();
        $query->filter = new Criterion\VisibleOnly();
        $query->sortClauses = [
            new SortClause\URL(),
        ];
        $query->offset = 0;
        $query->limit = $limit;

        $totalCount = $this->getTotalCount(clone $query);

        $progress = new ProgressBar($output, $totalCount);
        $progress->start();
        while ($query->offset < $totalCount) {
            $this->getUrlHandler()->check($query);

            $progress->advance(min($limit, $totalCount - $query->offset));
            $query->offset += $limit;
        }
        $progress->finish();
    }

    private function getTotalCount(URLQuery $query)
    {
        $repository = $this->getContainer()->get('ezpublish.api.repository');
        /** @var URLService $urlService */
        $urlService = $repository->getURLService();

        $query->limit = 0;

        return $urlService->findUrls($query)->totalCount;
    }

    /**
     * @return \eZ\Bundle\EzPublishCoreBundle\URLChecker\URLCheckerInterface
     */
    private function getUrlHandler()
    {
        return $this->getContainer()->get('ezpublish.url_checker');
    }
}
