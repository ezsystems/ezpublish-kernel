<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Features\Context;

use Behat\Behat\Context\Context;
use EzSystems\PlatformBehatBundle\Context\RepositoryContext;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Exceptions as ApiExceptions;
use eZ\Publish\API\Repository\Repository;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert as Assertion;

/**
 * Sentences for Content Types.
 */
class ContentTypeContext implements Context
{
    use RepositoryContext;

    /**
     * Default ContentTypeGroup.
     */
    const DEFAULT_GROUP = 'Content';

    /**
     * Default language code.
     */
    const DEFAULT_LANGUAGE = 'eng-GB';

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    protected $contentTypeService;

    /** @var \EzSystems\BehatBundle\Context\Object\ContentTypeGroup */
    protected $contentTypeGroupContext;

    /**
     * @injectService $repository @ezpublish.api.repository
     * @injectService $contentTypeService @ezpublish.api.service.content_type
     */
    public function __construct(Repository $repository, ContentTypeService $contentTypeService)
    {
        $this->setRepository($repository);
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * @Given (that) a Content Type exists with identifier :identifier with fields:
     * @Given (that) a Content Type exists with identifier :identifier in Group with identifier :groupIdentifier with fields:
     *     |   Identifier   |     Type       |     Name      |
     *     |  title         |  ezstring      |  Title        |
     *     |  body          |  ezxml         |  Body         |
     *
     * Makes sure a content type with $identifier and with the provided $fields definition.
     */
    public function ensureContentTypeWithIndentifier(
        $identifier,
        TableNode $fields,
        $groupIdentifier = self::DEFAULT_GROUP
    ) {
        $identifier = strtolower($identifier);
        $contentType = $this->loadContentTypeByIdentifier($identifier, false);

        if (!$contentType) {
            $contentType = $this->createContentType($groupIdentifier, $identifier, $fields);
        }

        return $contentType;
    }

    /**
     * @Given (that) a Content Type does not exist with identifier :identifier
     *
     * Makes sure a content type with $identifier does not exist.
     * If it exists deletes it.
     */
    public function ensureContentTypeDoesntExist($identifier)
    {
        $contentType = $this->loadContentTypeByIdentifier($identifier, false);
        if ($contentType) {
            $this->removeContentType($contentType);
        }
    }

    /**
     * @Then Content Type (with identifier) :identifier exists
     *
     * Verifies that a content type with $identifier exists.
     */
    public function assertContentTypeExistsByIdentifier($identifier)
    {
        Assertion::assertTrue(
            $this->checkContentTypeExistenceByIdentifier($identifier),
            "Couldn't find a Content Type with identifier '$identifier'."
        );
    }

    /**
     * @Then Content Type (with identifier) :identifier does not exist
     *
     * Verifies that a content type with $identifier does not exist.
     */
    public function assertContentTypeDoesntExistsByIdentifier($identifier)
    {
        Assertion::assertFalse(
            $this->checkContentTypeExistenceByIdentifier($identifier),
            "Found a Content Type with identifier '$identifier'."
        );
    }

    /**
     * @Then Content Type (with identifier) :identifier exists in Group with identifier :groupIdentifier
     *
     * Verifies that a content type with $identifier exists in group with identifier $groupIdentifier.
     */
    public function assertContentTypeExistsByIdentifierOnGroup($identifier, $groupIdentifier)
    {
        Assertion::assertTrue(
            $this->checkContentTypeExistenceByIdentifier($identifier, $groupIdentifier),
            "Couldn't find Content Type with identifier '$identifier' on '$groupIdentifier."
        );
    }

    /**
     * Load and return a content type by its identifier.
     *
     * @param  string  $identifier       content type identifier
     * @param  bool $throwIfNotFound  if true, throws an exception if it is not found.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup|null
     */
    protected function loadContentTypeByIdentifier($identifier, $throwIfNotFound = true)
    {
        $contentType = null;
        try {
            $contentType = $this->contentTypeService->loadContentTypeByIdentifier($identifier);
        } catch (ApiExceptions\NotFoundException $e) {
            $notFoundException = $e;
        }

        if (!$contentType && $throwIfNotFound) {
            throw $notFoundException;
        }

        return $contentType;
    }

    /**
     * Creates a content type with $identifier on content type group with identifier $groupIdentifier and with the
     * given 'fields' definitions.
     *
     * @param  string $groupIdentifier content type group identifier
     * @param  string $identifier      content type identifier
     * @param  array $fields           content type fields definitions
     *
     * @return eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function createContentType($groupIdentifier, $identifier, $fields)
    {
        $contentTypeService = $this->contentTypeService;
        $contentTypeGroup = $contentTypeService->loadContentTypeGroupByIdentifier($groupIdentifier);
        // convert 'some_type' to 'Some Type';
        $contentTypeName = ucwords(str_replace('_', ' ', $identifier));

        $contentTypeCreateStruct = $contentTypeService->newContentTypeCreateStruct($identifier);
        $contentTypeCreateStruct->mainLanguageCode = self::DEFAULT_LANGUAGE;
        $contentTypeCreateStruct->names = [self::DEFAULT_LANGUAGE => $contentTypeName];

        $fieldPosition = 0;
        foreach ($fields as $field) {
            $field = array_change_key_case($field, CASE_LOWER);
            $fieldPosition += 10;

            $fieldCreateStruct = $contentTypeService
                ->newFieldDefinitionCreateStruct($field['identifier'], $field['type']);
            $fieldCreateStruct->names = [self::DEFAULT_LANGUAGE => $field['name']];
            $fieldCreateStruct->position = $fieldPosition;
            if (isset($field['required'])) {
                $fieldCreateStruct->isRequired = ($field['required'] === 'true');
            }
            if (isset($field['validator']) && $field['validator'] !== 'false') {
                $fieldCreateStruct->validatorConfiguration = $this->processValidator($field['validator']);
            }
            if (isset($field['settings']) && $field['settings'] !== 'false') {
                $fieldCreateStruct->fieldSettings = $this->processSettings($field['settings']);
            }
            $contentTypeCreateStruct->addFieldDefinition($fieldCreateStruct);
        }

        $contentTypeDraft = $contentTypeService->createContentType(
            $contentTypeCreateStruct,
            [$contentTypeGroup]
        );
        $contentTypeService->publishContentTypeDraft($contentTypeDraft);

        $contentType = $contentTypeService->loadContentTypeByIdentifier($identifier);

        return $contentType;
    }

    /**
     * Remove the given 'ContentType' object.
     *
     * @param  eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     */
    protected function removeContentType($contentType)
    {
        try {
            $this->contentTypeService->deleteContentType($contentType);
        } catch (ApiExceptions\NotFoundException $e) {
            // nothing to do
        }
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup
     */
    protected function assignContentGroupTypeToContentType($contentType, $contentTypeGroup)
    {
        try {
            $this->contentTypeService->assignContentTypeGroup($contentType, $contentTypeGroup);
        } catch (ApiExceptions\InvalidArgumentException $exception) {
            //do nothing
        }
    }

    /**
     * Verifies that a content type with $identifier exists.
     *
     * @param string $identifier
     * @return bool
     */
    protected function checkContentTypeExistenceByIdentifier($identifier, $groupIdentifier = null)
    {
        $contentType = $this->loadContentTypeByIdentifier($identifier, false);
        if ($contentType && $groupIdentifier) {
            $contentTypeGroups = $contentType->getContentTypeGroups();
            foreach ($contentTypeGroups as $contentTypeGroup) {
                if ($contentTypeGroup->identifier == $groupIdentifier) {
                    return true;
                }
            }

            return false;
        }

        return $contentType ? true : false;
    }
}
