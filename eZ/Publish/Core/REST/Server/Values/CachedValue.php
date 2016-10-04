<?php

/**
 * File containing the CachedValue class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\REST\Common\Value as RestValue;

class CachedValue extends RestValue
{
    /**
     * Actual value object.
     *
     * @var mixed
     */
    public $value;

    /**
     * Associative array of cache tags.
     * Example: array( 'location' => 59, 'content' =>  55).
     *
     * @var mixed[]
     */
    public $cacheTags;

    /**
     * @param mixed $value The value that gets cached
     * @param array $cacheTags Tags to add to the cache (supported: locationId)
     * @throw InvalidArgumentException If invalid cache tags are provided
     */
    public function __construct($value, array $cacheTags = array())
    {
        $this->value = $value;
        $this->cacheTags = $this->checkCacheTags($cacheTags);
    }

    protected function checkCacheTags($tags)
    {
        if (!empty($tags['locationId'])) {
            // locationId is @deprecated (we can't call trigger_error as it will output text in response even in prod)
            $tags['location'] = $tags['locationId'];
            unset($tags['locationId']);
        }

        // @todo make this extensible
        $invalidTags = array_diff(array_keys($tags), ['location', 'content', 'content-type', 'parent', 'path', 'content-type-group']);
        if (count($invalidTags) > 0) {
            throw new InvalidArgumentException(
                'cacheTags',
                'Unknown cache tag(s): ' . implode(', ', $invalidTags)
            );
        }

        return $tags;
    }
}
