<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Command;

use Doctrine\DBAL\Connection;
use Exception;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use PDO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupVersionsCommand extends Command
{
    const DEFAULT_REPOSITORY_USER = 'admin';

    const VERSION_DRAFT = 'draft';
    const VERSION_ARCHIVED = 'archived';
    const VERSION_PUBLISHED = 'published';
    const VERSION_ALL = 'all';

    const VERSION_STATUS = [
        self::VERSION_DRAFT => VersionInfo::STATUS_DRAFT,
        self::VERSION_ARCHIVED => VersionInfo::STATUS_ARCHIVED,
        self::VERSION_PUBLISHED => VersionInfo::STATUS_PUBLISHED,
    ];

    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \Doctrine\DBAL\Driver\Connection */
    private $connection;

    public function __construct(
        Repository $repository,
        ConfigResolverInterface $configResolver,
        Connection $connection
    ) {
        $this->repository = $repository;
        $this->configResolver = $configResolver;
        $this->connection = $connection;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('ezplatform:content:cleanup-versions')
            ->setDescription('Remove unwanted content versions. It keeps published version untouched. By default, it keeps also the last archived/draft version.')
            ->addOption(
                'status',
                't',
                InputOption::VALUE_OPTIONAL,
                sprintf(
                    "Select which version types should be removed: '%s', '%s', '%s'.",
                    self::VERSION_DRAFT,
                    self::VERSION_ARCHIVED,
                    self::VERSION_ALL
                ),
                self::VERSION_ALL
            )
            ->addOption(
                'keep',
                'k',
                InputOption::VALUE_OPTIONAL,
                "Sets number of the most recent versions (both drafts and archived) which won't be removed.",
                'config_default'
            )
            ->addOption(
                'user',
                'u',
                InputOption::VALUE_OPTIONAL,
                'eZ Platform username (with Role containing at least Content policies: remove, read, versionread)',
                self::DEFAULT_REPOSITORY_USER
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // We don't load repo services or config resolver before execute() to avoid loading before SiteAccess is set.
        $keep = $input->getOption('keep');
        if ($keep === 'config_default') {
            $keep = $this->configResolver->getParameter('options.default_version_archive_limit');
        }

        if (($keep = (int) $keep) < 0) {
            throw new InvalidArgumentException(
                'keep',
                'Keep value can not be negative.'
            );
        }

        $userService = $this->repository->getUserService();
        $contentService = $this->repository->getContentService();
        $permissionResolver = $this->repository->getPermissionResolver();

        $permissionResolver->setCurrentUserReference(
            $userService->loadUserByLogin($input->getOption('user'))
        );

        $status = $input->getOption('status');

        $contentIds = $this->getObjectsIds($keep, $status);
        $contentIdsCount = count($contentIds);

        if ($contentIdsCount === 0) {
            $output->writeln('<info>There is no Content matching given criteria.</info>');

            return;
        }

        $output->writeln(sprintf(
            '<info>Found %d Content IDs matching given criteria.</info>',
            $contentIdsCount
        ));

        $removedVersionsCounter = 0;

        $removeAll = $status === self::VERSION_ALL;
        $removeDrafts = $status === self::VERSION_DRAFT;
        $removeArchived = $status === self::VERSION_ARCHIVED;

        foreach ($contentIds as $contentId) {
            try {
                $contentInfo = $contentService->loadContentInfo((int) $contentId);
                $versions = $contentService->loadVersions($contentInfo);
                $versionsCount = count($versions);

                $output->writeln(sprintf(
                    '<info>Content %d has %d version(s)</info>',
                    (int) $contentId,
                    $versionsCount
                ), Output::VERBOSITY_VERBOSE);

                $versions = array_filter($versions, function ($version) use ($removeAll, $removeDrafts, $removeArchived) {
                    if (
                        ($removeAll && $version->status !== VersionInfo::STATUS_PUBLISHED) ||
                        ($removeDrafts && $version->status === VersionInfo::STATUS_DRAFT) ||
                        ($removeArchived && $version->status === VersionInfo::STATUS_ARCHIVED)
                    ) {
                        return $version;
                    }
                });

                if ($keep > 0) {
                    $versions = array_slice($versions, 0, -$keep);
                }

                $output->writeln(sprintf(
                    "Found %d content's (%d) version(s) to remove.",
                    count($versions),
                    (int) $contentId
                ), Output::VERBOSITY_VERBOSE);

                /** @var \eZ\Publish\API\Repository\Values\Content\VersionInfo $version */
                foreach ($versions as $version) {
                    $contentService->deleteVersion($version);
                    ++$removedVersionsCounter;
                    $output->writeln(sprintf(
                        "Content's (%d) version (%d) has been deleted.",
                        $contentInfo->id,
                        $version->id
                    ), Output::VERBOSITY_VERBOSE);
                }
            } catch (Exception $e) {
                $output->writeln(sprintf(
                    '<error>%s</error>',
                    $e->getMessage()
                ));
            }
        }

        $output->writeln(sprintf(
            '<info>Removed %d unwanted contents version(s).</info>',
            $removedVersionsCounter
        ));
    }

    /**
     * @param int $keep
     * @param string $status
     *
     * @return array
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    protected function getObjectsIds($keep, $status)
    {
        $query = $this->connection->createQueryBuilder()
                ->select('c.id')
                ->from('ezcontentobject', 'c')
                ->join('c', 'ezcontentobject_version', 'v', 'v.contentobject_id = c.id')
                ->groupBy('c.id', 'v.status')
                ->having('count(c.id) > :keep');
        $query->setParameter('keep', $keep);

        if ($status !== self::VERSION_ALL) {
            $query->where('v.status = :status');
            $query->setParameter('status', $this->mapStatusToVersionInfoStatus($status));
        } else {
            $query->andWhere('v.status != :status');
            $query->setParameter('status', $this->mapStatusToVersionInfoStatus(self::VERSION_PUBLISHED));
        }

        $stmt = $query->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @param string $status
     *
     * @return int
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    private function mapStatusToVersionInfoStatus($status)
    {
        if (array_key_exists($status, self::VERSION_STATUS)) {
            return self::VERSION_STATUS[$status];
        }

        throw new InvalidArgumentException(
            'status',
            sprintf(
                "Status %s can't be mapped to VersionInfo status.",
                $status
            )
        );
    }
}
