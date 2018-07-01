<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Content;
use RuntimeException;

class ContentContext implements Context, SnippetAcceptingContext
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Content */
    private $currentContent;

    /** @var \eZ\Publish\API\Repository\Values\Content\Content */
    private $currentDraft;

    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @Given /^I create an folder draft$/
     */
    public function iCreateAnFolderDraft()
    {
        $this->currentDraft = $this->createDraft(
            'folder',
            [
                'name' => 'Preview draft ' . date('c'),
                'short_description' => '<?xml version="1.0" encoding="UTF-8"?><section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0"><para>This is a paragraph.</para></section>',
            ]
        );
    }

    /**
     * @Given /^I create a draft of an existing content item$/
     */
    public function iCreateADraftOfAnExistingContentItem()
    {
        $this->currentContent = $this->createContentItem(
            'folder',
            ['name' => 'BDD preview test']
        );

        $this->currentDraft = $this->createDraftForContent($this->currentContent);
    }

    /**
     * Uses a content type identifier + a hash of fields values
     * to create and publish a content item below the root location.
     *
     * @param string $contentTypeIdentifier
     * @param array $fields Hash of field def identifier => field value
     *
     * @return Content the created content item.
     */
    public function createContentItem($contentTypeIdentifier, array $fields)
    {
        $draft = $this->createDraft($contentTypeIdentifier, $fields);

        $this->currentContent = $this->repository->sudo(
            function () use ($draft) {
                return $this->repository->getContentService()->publishVersion($draft->versionInfo);
            }
        );

        $this->currentDraft = null;

        return $this->currentContent;
    }

    public function createDraftForContent(Content $content)
    {
        $this->currentDraft = $this->repository->sudo(
            function () use ($content) {
                return $this->repository->getContentService()->createContentDraft($content->contentInfo);
            }
        );

        return $this->currentDraft;
    }

    public function getCurrentDraft()
    {
        if ($this->currentDraft === null) {
            throw new RuntimeException('No current draft has been set');
        }

        return $this->currentDraft;
    }

    public function updateDraft($fields)
    {
        $contentService = $this->repository->getContentService();

        $updateStruct = $contentService->newContentUpdateStruct();
        foreach ($fields as $fieldDefIdentifier => $fieldValueUpdate) {
            $updateStruct->setField($fieldDefIdentifier, $fieldValueUpdate);
        }

        $updatedDraft = $this->repository->sudo(function () use ($updateStruct) {
            return $this->repository->getContentService()->updateContent(
                $this->currentDraft->versionInfo,
                $updateStruct
            );
        });

        return $this->currentDraft = $updatedDraft;
    }

    /**
     * Uses a content type identifier + a hash of fields values
     * to create and publish a draft below the root location.
     *
     * @param string $contentTypeIdentifier
     * @param array $fields Hash of field def identifier => field value
     *
     * @return Content the created draft.
     */
    public function createDraft($contentTypeIdentifier, array $fields)
    {
        $contentService = $this->repository->getContentService();

        $createStruct = $contentService->newContentCreateStruct(
            $this->repository->getContentTypeService()->loadContentTypeByIdentifier($contentTypeIdentifier),
            'eng-GB'
        );

        foreach ($fields as $fieldDefIdentifier => $fieldValue) {
            $createStruct->setField($fieldDefIdentifier, $fieldValue);
        }

        $locationCreateStruct = $this->repository->getLocationService()->newLocationCreateStruct(2);

        $this->currentDraft = $this->repository->sudo(
            function () use ($createStruct, $locationCreateStruct) {
                return $this->repository->getContentService()->createContent(
                    $createStruct,
                    [$locationCreateStruct]
                );
            }
        );

        return $this->currentDraft;
    }
}
