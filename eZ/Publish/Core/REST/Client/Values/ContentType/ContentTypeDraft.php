<?php

/**
 * File containing the ContentTypeDraft class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Client\Values\ContentType;

use eZ\Publish\API\Repository\Values;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft as APIContentTypeDraft;
use eZ\Publish\Core\Repository\Values\MultiLanguageTrait;

/**
 * This class represents a draft of a content type.
 */
class ContentTypeDraft extends APIContentTypeDraft
{
    use MultiLanguageTrait;

    /**
     * ContentType encapsulated in the draft.
     *
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    protected $innerContentType;

    /**
     * Creates a new draft with $innerContentType.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $innerContentType
     */
    public function __construct(Values\ContentType\ContentType $innerContentType)
    {
        parent::__construct([]);
        $this->innerContentType = $innerContentType;
    }

    /**
     * Returns the inner content type.
     *
     * ONLY FOR INTERNAL USE IN THE INTEGRATION TEST SUITE.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function getInnerContentType()
    {
        return $this->innerContentType;
    }

    /**
     * {@inheritdoc}
     */
    public function getNames()
    {
        return $this->innerContentType->getNames();
    }

    /**
     * {@inheritdoc}
     */
    public function getName($languageCode = null)
    {
        return $this->innerContentType->getName($languageCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescriptions()
    {
        return $this->innerContentType->getDescriptions();
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($languageCode = null)
    {
        return $this->innerContentType->getDescription($languageCode);
    }

    /**
     * This method returns the content type groups this content type is assigned to.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[]
     */
    public function getContentTypeGroups()
    {
        return $this->innerContentType->getContentTypeGroups();
    }

    /**
     * This method returns the content type field definitions from this type.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[]
     */
    public function getFieldDefinitions()
    {
        return $this->innerContentType->getFieldDefinitions();
    }

    /**
     * This method returns the field definition for the given identifier.
     *
     * @param string $fieldDefinitionIdentifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition
     */
    public function getFieldDefinition($fieldDefinitionIdentifier)
    {
        return $this->innerContentType->getFieldDefinition($fieldDefinitionIdentifier);
    }

    public function __get($propertyName)
    {
        return $this->innerContentType->$propertyName;
    }

    public function __set($propertyName, $propertyValue)
    {
        $this->innerContentType->$propertyName = $propertyValue;
    }

    public function __isset($propertyName)
    {
        return isset($this->innerContentType->$propertyName);
    }
}
