<?php

/**
 * File containing the eZ\Publish\Core\Repository\Values\Content\Content class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\Content\Content as APIContent;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;

/**
 * this class represents a content object in a specific version.
 *
 * @property-read \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo convenience getter for $versionInfo->contentInfo
 * @property-read \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType convenience getter for $versionInfo->contentInfo->contentType
 * @property-read mixed $id convenience getter for retrieving the contentId: $versionInfo->content->id
 * @property-read \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo calls getVersionInfo()
 * @property-read \eZ\Publish\API\Repository\Values\Content\Field[] $fields Access fields, calls getFields()
 *
 * @internal Meant for internal use by Repository, type hint against API object instead.
 */
class Content extends APIContent
{
    /**
     * @var mixed[][] An array of array of field values like[$fieldDefIdentifier][$languageCode]
     */
    protected $fields;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    protected $versionInfo;

    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    protected $contentType;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Field[] An array of {@link Field}
     */
    private $internalFields = array();

    /**
     * The first matched field language among user provided prioritized languages.
     *
     * The first matched language among user provided prioritized languages on object retrieval, or null if none
     * provided (all languages) or on main fallback.
     *
     * @internal
     * @var string|null
     */
    protected $prioritizedFieldLanguageCode;

    public function __construct(array $data = array())
    {
        foreach ($data as $propertyName => $propertyValue) {
            $this->$propertyName = $propertyValue;
        }
        foreach ($this->internalFields as $field) {
            $this->fields[$field->fieldDefIdentifier][$field->languageCode] = $field->value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getVersionInfo()
    {
        return $this->versionInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType(): ContentType
    {
        return $this->contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldValue($fieldDefIdentifier, $languageCode = null)
    {
        if (null === $languageCode) {
            $languageCode = $this->prioritizedFieldLanguageCode ?: $this->versionInfo->contentInfo->mainLanguageCode;
        }

        if (isset($this->fields[$fieldDefIdentifier][$languageCode])) {
            return $this->fields[$fieldDefIdentifier][$languageCode];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getFields()
    {
        return $this->internalFields;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldsByLanguage($languageCode = null)
    {
        $fields = array();

        if (null === $languageCode) {
            $languageCode = $this->prioritizedFieldLanguageCode ?: $this->versionInfo->contentInfo->mainLanguageCode;
        }

        foreach ($this->getFields() as $field) {
            if ($field->languageCode !== $languageCode) {
                continue;
            }
            $fields[$field->fieldDefIdentifier] = $field;
        }

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function getField($fieldDefIdentifier, $languageCode = null)
    {
        if (null === $languageCode) {
            $languageCode = $this->prioritizedFieldLanguageCode ?: $this->versionInfo->contentInfo->mainLanguageCode;
        }

        foreach ($this->getFields() as $field) {
            if ($field->fieldDefIdentifier === $fieldDefIdentifier
                && $field->languageCode === $languageCode) {
                return $field;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function getProperties($dynamicProperties = array('id', 'contentInfo'))
    {
        return parent::getProperties($dynamicProperties);
    }

    /**
     * {@inheritdoc}
     */
    public function __get($property)
    {
        switch ($property) {
            case 'id':
                return $this->versionInfo->contentInfo->id;

            case 'contentInfo':
                return $this->versionInfo->contentInfo;
        }

        return parent::__get($property);
    }

    /**
     * {@inheritdoc}
     */
    public function __isset($property)
    {
        if ($property === 'id') {
            return true;
        }

        if ($property === 'contentInfo') {
            return true;
        }

        return parent::__isset($property);
    }
}
