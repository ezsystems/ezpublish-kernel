<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Command;

use Doctrine\DBAL\Connection;
use Exception;
use eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use PDO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupVersionsCommand extends Command
{
    const DEFAULT_REPOSITORY_USER = 'admin';
    const DEFAULT_EXCLUDED_CONTENT_TYPES = 'user';

    const BEFORE_RUNNING_HINTS = <<<EOT
<error>Before you continue:</error>
- Make sure to back up your database.
- Take installation offline, during the script execution the database should not be modified.
- Run this command without memory limit.
- Run this command in production environment using <info>--env=prod</info>
EOT;

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

    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider
     */
    private $repositoryConfigurationProvider;

    /** @var \Doctrine\DBAL\Driver\Connection */
    private $connection;

    public function __construct(
        Repository $repository,
        RepositoryConfigurationProvider $repositoryConfigurationProvider,
        Connection $connection
    ) {
        $this->repository = $repository;
        $this->repositoryConfigurationProvider = $repositoryConfigurationProvider;
        $this->connection = $connection;

        parent::__construct();
    }

    protected function configure()
    {
        $beforeRunningHints = self::BEFORE_RUNNING_HINTS;
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
            )
            ->addOption(
                'excluded-content-types',
                null,
                InputOption::VALUE_OPTIONAL,
                'Comma separated list of ContentType identifiers of which versions should not be removed, for instance `article`.',
                self::DEFAULT_EXCLUDED_CONTENT_TYPES
            )->setHelp(
                <<<EOT
The command <info>%command.name%</info> reduces content versions to a minimum. 
It keeps published version untouched, and by default it keeps also the last archived/draft version.
Note: This script can potentially run for a very long time, and in Symfony dev environment it will consume memory exponentially with size of dataset.

{$beforeRunningHints}
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // We don't load repo services or config resolver before execute() to avoid loading before SiteAccess is set.
        $keep = $input->getOption('keep');
        if ($keep === 'config_default') {
            $config = $this->repositoryConfigurationProvider->getRepositoryConfig();
            $keep = $config['options']['default_version_archive_limit'];
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

        $excludedContentTypeIdentifiers = explode(',', $input->getOption('excluded-content-types'));
        $contentIds = $this->getObjectsIds($keep, $status, $excludedContentTypeIdentifiers);
        $contentIdsCount = count($contentIds);

        if ($contentIdsCount === 0) {
            $output->writeln('<info>There is no Content matching given criteria.</info>');

            return;
        }

        $output->writeln(sprintf(
            '<info>Found %d Content IDs matching given criteria.</info>',
            $contentIdsCount
        ));

        $displayProgressBar = !($output->isVerbose() || $output->isVeryVerbose() || $output->isDebug());

        if ($displayProgressBar) {
            $progressBar = new ProgressBar($output, $contentIdsCount);
            $progressBar->setFormat(
                '%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%' . PHP_EOL
            );
            $progressBar->start();
        }

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
                ), OutputInterface::VERBOSITY_VERBOSE);

                $versions = array_filter($versions, function ($version) use ($removeAll, $removeDrafts, $removeArchived) {
                    if (
                        ($removeAll && $version->status !== VersionInfo::STATUS_PUBLISHED) ||
                        ($removeDrafts && $version->status === VersionInfo::STATUS_DRAFT) ||
                        ($removeArchived && $version->status === VersionInfo::STATUS_ARCHIVED)
                    ) {
                        return true;
                    }
                });

                if ($keep > 0) {
                    $versions = array_slice($versions, 0, -$keep);
                }

                $output->writeln(sprintf(
                    "Found %d content's (%d) version(s) to remove.",
                    count($versions),
                    (int) $contentId
                ), OutputInterface::VERBOSITY_VERBOSE);

                /** @var \eZ\Publish\API\Repository\Values\Content\VersionInfo $version */
                foreach ($versions as $version) {
                    $contentService->deleteVersion($version);
                    ++$removedVersionsCounter;
                    $output->writeln(sprintf(
                        "Content's (%d) version (%d) has been deleted.",
                        $contentInfo->id,
                        $version->id
                    ), OutputInterface::VERBOSITY_VERBOSE);
                }

                if ($displayProgressBar) {
                    $progressBar->advance(1);
                }
            } catch (Exception $e) {
                $output->writeln(sprintf(
                    '<error>%s</error>',
                    $e->getMessage()
                ));
            }
        }

        $output->writeln(sprintf(
            '<info>Removed %d unwanted contents version(s) from %d content(s).</info>',
            $removedVersionsCounter,
            $contentIdsCount
        ));
    }

    /**
     * @param int $keep
     * @param string $status
     * @param string[] $excludedContentTypes
     *
     * @return array
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    protected function getObjectsIds($keep, $status, $excludedContentTypes = [])
    {
        $query = $this->connection->createQueryBuilder()
                ->select('c.id')
                ->from('ezcontentobject', 'c')
                ->join('c', 'ezcontentobject_version', 'v', 'v.contentobject_id = c.id')
                ->join('c', 'ezcontentclass', 'cl', 'cl.id = c.contentclass_id')
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

        if ($excludedContentTypes) {
            $expr = $query->expr();
            $query
                ->andWhere(
                    $expr->notIn(
                        'cl.identifier',
                        ':contentTypes'
                    )
                )->setParameter(':contentTypes', $excludedContentTypes, Connection::PARAM_STR_ARRAY);
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
