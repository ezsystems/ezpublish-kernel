<?php

/**
 * File containing the ContentTypeGroup class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Client\Values\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup as APIContentTypeGroup;

/**
 * REST Client ValueObject representing Content Type Group.
 */
class ContentTypeGroup extends APIContentTypeGroup
{
    /** @var string[] */
    protected $names;

    /** @var string[] */
    protected $descriptions;

    /**
     * {@inheritdoc}
     */
    public function getNames()
    {
        return $this->names;
    }

    /**
     * {@inheritdoc}
     */
    public function getName($languageCode = null)
    {
        // @todo Make this respect language priority list?
        return isset($this->names[$languageCode]) ? $this->names[$languageCode] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescriptions()
    {
        return $this->descriptions;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($languageCode = null)
    {
        return isset($this->descriptions[$languageCode]) ? $this->descriptions[$languageCode] : null;
    }
}
