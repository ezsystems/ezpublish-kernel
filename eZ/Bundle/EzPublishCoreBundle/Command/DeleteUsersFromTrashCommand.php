<?php
/**
 * File containing the DeleteUsersFromTrashCommand class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Command;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use eZ\Publish\API\Repository\Values\Content\Query;

class DeleteUsersFromTrashCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ezplatform:upgrade:delete-users-from-trash')
            ->setDescription('Deletes all users from trash, See https://jira.ez.no/browse/EZP-25643');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');
        $trashService = $repository->getTrashService();
        $contentTypeService = $repository->getContentTypeService();

        $repository->sudo(
            function () use ($contentTypeService, $trashService, $output) {
                $contentTypes = $this->getContentTypesEzWithUserField($contentTypeService);

                $query = new Query();
                $query->filter = !empty($contentTypes) ? new Query\Criterion\ContentTypeIdentifier($contentTypes) : null;
                $query->limit = PHP_INT_MAX;

                $trashItems = $trashService->findTrashItems($query);

                foreach ($trashItems->items as $trashItem) {
                    $output->writeln('Deleting ' . $trashItem->contentInfo->name . ' from trash');
                    $trashService->deleteTrashItem($trashItem);
                }
            }
        );
    }

    /**
     * Returns all contentTypes having an ezuser field.
     *
     * @return array
     */
    protected function getContentTypesEzWithUserField(ContentTypeService $contentTypeService)
    {
        $contentTypeIdentifiers = [];

        $contentTypeGroups = $contentTypeService->loadContentTypeGroups();
        foreach ($contentTypeGroups as $contentTypeGroup) {
            $contentTypeList = $contentTypeService->loadContentTypes($contentTypeGroup);

            foreach ($contentTypeList as $contentType) {
                if ($this->hasEzUserField($contentType)) {
                    $contentTypeIdentifiers[] = $contentType->identifier;
                    continue;
                }
            }
        }

        return $contentTypeIdentifiers;
    }

    /**
     * Checks if contentType has any field of ezuser type.
     *
     * @param ContentType $contentType
     * @return bool
     */
    protected function hasEzUserField(ContentType $contentType)
    {
        /** @var \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition[] $fieldDefinitions */
        $fieldDefinitions = $contentType->getFieldDefinitions();
        foreach ($fieldDefinitions as $fieldDefinition) {
            if ($fieldDefinition->fieldTypeIdentifier === 'ezuser') {
                return true;
            }
        }

        return false;
    }
}
