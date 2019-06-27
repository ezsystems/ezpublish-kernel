<?php

/**
 * File containing the ObjectState class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Values\ObjectState;

use eZ\Publish\API\Repository\Values\ObjectState\ObjectState as APIObjectState;
use eZ\Publish\Core\Repository\Values\MultiLanguageDescriptionTrait;
use eZ\Publish\Core\Repository\Values\MultiLanguageNameTrait;
use eZ\Publish\Core\Repository\Values\MultiLanguageTrait;

/**
 * This class represents a object state value.
 *
 * @property-read mixed $id the id of the content type group
 * @property-read string $identifier the identifier of the content type group
 * @property-read int $priority the priority in the group ordering
 * @property-read string $mainLanguageCode the default language of the object state names and descriptions used for fallback.
 * @property-read string $defaultLanguageCode deprecated, use $mainLanguageCode
 * @property-read string[] $languageCodes the available languages
 *
 * @internal Meant for internal use by Repository, type hint against API object instead.
 */
class ObjectState extends APIObjectState
{
    use MultiLanguageTrait;
    use MultiLanguageNameTrait;
    use MultiLanguageDescriptionTrait;

    /** @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup */
    protected $objectStateGroup;

    /**
     * The object state group this object state belongs to.
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup
     */
    public function getObjectStateGroup()
    {
        return $this->objectStateGroup;
    }

    /**
     * Magic getter for BC reasons.
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property)
    {
        if ($property === 'defaultLanguageCode') {
            @trigger_error(
                __CLASS__ . '::$defaultLanguageCode is deprecated. Use mainLanguageCode',
                E_USER_DEPRECATED
            );

            return $this->mainLanguageCode;
        }

        return parent::__get($property);
    }

    /**
     * Magic isset for BC reasons.
     *
     * @param string $property
     * @return bool
     */
    public function __isset($property)
    {
        if ($property === 'defaultLanguageCode') {
            @trigger_error(
                __CLASS__ . '::$defaultLanguageCode is deprecated. Use mainLanguageCode'
            );

            return true;
        }

        return parent::__isset($property);
    }
}
