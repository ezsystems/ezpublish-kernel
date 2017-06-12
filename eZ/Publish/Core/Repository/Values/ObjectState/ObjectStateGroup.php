<?php

/**
 * File containing the ObjectStateGroup class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Values\ObjectState;

use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup as APIObjectStateGroup;
use eZ\Publish\Core\Repository\Values\MultiLanguageDescriptionTrait;
use eZ\Publish\Core\Repository\Values\MultiLanguageNameTrait;
use eZ\Publish\Core\Repository\Values\MultiLanguageTrait;

/**
 * This class represents an object state group value.
 *
 * @property-read mixed $id the id of the content type group
 * @property-read string $identifier the identifier of the content type group
 * @property-read string $mainLanguageCode the default language of the object state group names and description used for fallback.
 * @property-read string $defaultLanguageCode deprecated, use $mainLanguageCode
 * @property-read string[] $languageCodes the available languages
 *
 * @internal Meant for internal use by Repository, type hint against API object instead.
 */
class ObjectStateGroup extends APIObjectStateGroup
{
    use MultiLanguageTrait;
    use MultiLanguageNameTrait;
    use MultiLanguageDescriptionTrait;

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

    public function __isset($property)
    {
        if ($property === 'defaultLanguageCode') {
            @trigger_error(
                __CLASS__ . '::$defaultLanguageCode is deprecated. Use mainLanguageCode',
                E_USER_DEPRECATED
            );

            return true;
        }

        return parent::__isset($property);
    }
}
