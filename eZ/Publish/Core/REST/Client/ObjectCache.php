<?php

/**
 * File containing the SimpleObjectCache class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Simple cache for value objects during a single request.
 */
class ObjectCache
{
    /**
     * Map of cached objects by their key.
     *
     * @var array
     */
    protected $cachedObjects = array();

    /**
     * Stores $data under $key.
     *
     * @param string $key
     * @param ValueObject $data
     */
    public function store($key, ValueObject $data)
    {
        $this->cachedObjects[$key] = $data;
    }

    /**
     * Restores data stored under $key, returns null if $key is not found.
     *
     * @param string $key
     *
     * @return ValueObject|null
     */
    public function restore($key)
    {
        if (isset($this->cachedObjects[$key])) {
            return $this->cachedObjects[$key];
        }

        return null;
    }

    /**
     * Clears the data stored in $key.
     *
     * @param string $key
     */
    public function clear($key)
    {
        unset($this->cachedObjects[$key]);
    }

    /**
     * Clears all cached items.
     */
    public function clearAll()
    {
        $this->cachedObjects = array();
    }
}
