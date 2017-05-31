<?php

/**
 * File containing the ContentTypeGroup class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Values\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup as APIContentTypeGroup;

class ContentTypeGroup extends APIContentTypeGroup
{
    protected $names;

    protected $descriptions;

    public function __construct(array $data = array())
    {
        foreach ($data as $propertyName => $propertyValue) {
            $this->$propertyName = $propertyValue;
        }
    }

    /**
     * {@inheritdoc}.
     */
    public function getNames()
    {
        return $this->names;
    }

    /**
     * {@inheritdoc}.
     */
    public function getName($languageCode = null)
    {
        return $this->names[$languageCode];
    }

    /**
     * {@inheritdoc}.
     */
    public function getDescriptions()
    {
        return $this->descriptions;
    }

    /**
     * {@inheritdoc}.
     */
    public function getDescription($languageCode = null)
    {
        return $this->descriptions[$languageCode];
    }
}
