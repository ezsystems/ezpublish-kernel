<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Values\User;

use eZ\Publish\API\Repository\Values\User\PolicyDraft as APIPolicyDraft;

/**
 * Class PolicyDraft.
 *
 * @internal Meant for internal use by Repository, type hint against API object instead.
 */
class PolicyDraft extends APIPolicyDraft
{
    /** @var \eZ\Publish\API\Repository\Values\User\Policy */
    protected $innerPolicy;

    /**
     * Set of properties that are specific to PolicyDraft.
     *
     * @var array
     */
    private $draftProperties = ['originalId' => true];

    public function __get($property)
    {
        if (isset($this->draftProperties[$property])) {
            return parent::__get($property);
        }

        return $this->innerPolicy->$property;
    }

    public function __set($property, $propertyValue)
    {
        if (isset($this->draftProperties[$property])) {
            parent::__set($property, $propertyValue);
        }

        $this->innerPolicy->$property = $propertyValue;
    }

    public function __isset($property)
    {
        if (isset($this->draftProperties[$property])) {
            return parent::__isset($property);
        }

        return $this->innerPolicy->__isset($property);
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\User\Limitation[]
     */
    public function getLimitations()
    {
        return $this->innerPolicy->getLimitations();
    }
}
