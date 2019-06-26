<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Command;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Console command for deep copying subtree from one location to another.
 */
class CopySubtreeCommand extends Command
{
    /** @var \eZ\Publish\API\Repository\LocationService */
    private $locationService;

    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    private $permissionResolver;

    /** @var \eZ\Publish\API\Repository\UserService */
    private $userService;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /**
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     * @param \eZ\Publish\API\Repository\PermissionResolver $permissionResolver
     * @param \eZ\Publish\API\Repository\UserService $userService
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     */
    public function __construct(
        LocationService $locationService,
        PermissionResolver $permissionResolver,
        UserService $userService,
        ContentTypeService $contentTypeService,
        SearchService $searchService
    ) {
        parent::__construct();
        $this->locationService = $locationService;
        $this->permissionResolver = $permissionResolver;
        $this->userService = $userService;
        $this->contentTypeService = $contentTypeService;
        $this->searchService = $searchService;
    }

    protected function configure()
    {
        $this
            ->setName('ezplatform:copy-subtree')
            ->addArgument(
                'source-location-id',
                InputArgument::REQUIRED,
                'Id of subtree root location'
            )
            ->addArgument(
                'target-location-id',
                InputArgument::REQUIRED,
                'Id of target location'
            )
            ->addOption(
                'user',
                'u',
                InputOption::VALUE_OPTIONAL,
                'eZ Platform username (with Role containing at least Content policies: create, read)',
                'admin'
            )
            ->setDescription('Copy subtree from one location to another');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->permissionResolver->setCurrentUserReference(
            $this->userService->loadUserByLogin($input->getOption('user'))
        );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|null
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sourceLocationId = $input->getArgument('source-location-id');
        $targetLocationId = $input->getArgument('target-location-id');

        $sourceLocation = $this->locationService->loadLocation($sourceLocationId);
        $targetLocation = $this->locationService->loadLocation($targetLocationId);

        if (stripos($targetLocation->pathString, $sourceLocation->pathString) !== false) {
            throw new InvalidArgumentException(
                'target-location-id',
                'Target parent location is a sub location of the source subtree'
            );
        }

        $targetContentType = $this->contentTypeService->loadContentType(
            $targetLocation->getContentInfo()->contentTypeId
        );

        if (!$targetContentType->isContainer) {
            throw new InvalidArgumentException(
                'target-location-id',
                'Cannot copy location to a parent that is not a container'
            );
        }
        $questionHelper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            sprintf(
                'Are you sure you want to copy `%s` subtree (no. of children: %d) into `%s`? This make take a while for big number of nested children [Y/n]? ',
                $sourceLocation->contentInfo->name,
                $this->getAllChildrenCount($sourceLocation),
                $targetLocation->contentInfo->name
            )
        );

        if (!$input->getOption('no-interaction') && !$questionHelper->ask($input, $output, $question)) {
            return 0;
        }

        $this->locationService->copySubtree(
            $sourceLocation,
            $targetLocation
        );

        $output->writeln(
            '<info>Finished</info>'
        );

        return 0;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return int
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    protected function getAllChildrenCount(Location $location): int
    {
        $query = new LocationQuery([
            'filter' => new Criterion\Subtree($location->pathString),
        ]);

        $searchResults = $this->searchService->findLocations($query);

        return $searchResults->totalCount;
    }
}
