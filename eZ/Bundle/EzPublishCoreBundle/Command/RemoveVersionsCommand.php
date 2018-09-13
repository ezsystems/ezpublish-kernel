<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\Command;

use Doctrine\DBAL\Connection;
use Exception;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use PDO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveVersionsCommand extends Command
{
    const DEFAULT_REPOSITORY_USER = 'admin';
    const DEFAULT_KEEP = 2;

    const VERSION_DRAFT = 'draft';
    const VERSION_ARCHIVED = 'archived';
    const VERSION_ALL = 'all';

    /**
     * @var \eZ\Publish\API\Repository\UserService
     */
    private $userService;

    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    private $contentService;

    /**
     * @var \eZ\Publish\API\Repository\PermissionResolver
     */
    private $permissionResolver;

    /**
     * @var \Doctrine\DBAL\Driver\Connection
     */
    private $connection;

    public function __construct(
        UserService $userService,
        ContentService $contentService,
        PermissionResolver $permissionResolver,
        Connection $connection
    ) {
        $this->userService = $userService;
        $this->contentService = $contentService;
        $this->permissionResolver = $permissionResolver;
        $this->connection = $connection;

        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->permissionResolver->setCurrentUserReference(
            $this->userService->loadUserByLogin($input->getOption('user'))
        );
    }

    protected function configure(): void
    {
        $this
            ->setName('ezplatform:remove-versions')
            ->setDescription('Remove unwanted content versions. It keeps published version untouched. By default, it keeps also the last archived/draft version.')
            ->addOption(
                'status',
                's',
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
                self::DEFAULT_KEEP
            )
            ->addOption(
                'user',
                'u',
                InputOption::VALUE_OPTIONAL,
                'eZ Platform username (with Role containing at least Content policies: remove, read, versionread)',
                self::DEFAULT_REPOSITORY_USER
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        if (($keep = (int) $input->getOption('keep')) < 0) {
            throw new InvalidArgumentException(
                'status',
                'Keep value can not be negative.'
            );
        }

        $status = $input->getOption('status');

        $contentIds = $this->getObjectsIds($keep, $status);
        $contentIdsCount = count($contentIds);

        if ($contentIdsCount === 0) {
            $output->writeln('<info>There is no Contents matching given criteria.</info>');

            return;
        }

        $output->writeln(sprintf(
            '<info>Found %d Content IDs matching given criteria.</info>',
            $contentIdsCount
        ));

        $removedVersionsCounter = 0;

        foreach ($contentIds as $contentData) {
            try {
                $contentInfo = $this->contentService->loadContentInfo($contentData['id']);
                $versions = $this->contentService->loadVersions($contentInfo);
                $versionsCount = count($versions);

                $output->writeln(sprintf(
                    '<info>Content %d has %d version(s)</info>',
                    $contentData['id'],
                    $versionsCount
                ), Output::VERBOSITY_VERBOSE);

                $versions = array_slice(array_reverse($versions), $keep);

                foreach ($versions as $version) {
                    if (
                        (!$version->isPublished() && $status === self::VERSION_ALL) ||
                        ($version->isDraft() && $status === self::VERSION_DRAFT) ||
                        ($version->isArchived() && $status === self::VERSION_ARCHIVED)
                    ) {
                        $this->contentService->deleteVersion($version);
                        ++$removedVersionsCounter;
                        $output->writeln(sprintf(
                            "Content's (%s) version (%s) has been deleted.",
                            $contentInfo->id,
                            $version->id
                        ), Output::VERBOSITY_VERBOSE);
                    }
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
    protected function getObjectsIds(int $keep, string $status): array
    {
        $query = $this->connection->createQueryBuilder()
                ->select('c.id, v.status')
                ->from('ezcontentobject', 'c')
                ->leftJoin('c', 'ezcontentobject_version', 'v', 'v.contentobject_id = c.id')
                ->groupBy('c.id', 'v.status')
                ->having('count(c.id) > :keep');
        $query->setParameter('keep', $keep);

        if ($status !== self::VERSION_ALL) {
            $query->where('v.status = :status');
            $query->setParameter('status', $this->getVersionInfoStatus($status));
        }

        $stmt = $query->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $status
     *
     * @return int
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    private function getVersionInfoStatus(string $status): int
    {
        if ($status === self::VERSION_ARCHIVED) {
            return VersionInfo::STATUS_ARCHIVED;
        }
        if ($status === self::VERSION_DRAFT) {
            return VersionInfo::STATUS_DRAFT;
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
