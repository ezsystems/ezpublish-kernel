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
            ->setName('ezpublish:deleteusersfromtrash')
            ->setDescription('Deletes all users from trash, See https://jira.ez.no/browse/EZP-25643');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');
        $trashService = $repository->getTrashService();
        $contentTypeService = $repository->getContentTypeService();

        $repository->setCurrentUser($repository->getUserService()->loadUser(14));

        $query = new Query();
        $query->filter = null;

        $trashItems = $trashService->findTrashItems($query);

        foreach ($trashItems->items as $trashItem) {
            $contentType = $contentTypeService->loadContentType(
                $trashItem->contentInfo->contentTypeId
            );

            if ($this->hasUserField($contentType)) {
                $output->writeln('Deleting ' . $trashItem->contentInfo->name .  ' from trash');
                $trashService->deleteTrashItem($trashItem);
            }
        }
    }

    /**
     * Checks if contentType has any field of ezuser type.
     *
     * @param ContentType $contentType
     * @return bool
     */
    private function hasUserField(ContentType $contentType)
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
