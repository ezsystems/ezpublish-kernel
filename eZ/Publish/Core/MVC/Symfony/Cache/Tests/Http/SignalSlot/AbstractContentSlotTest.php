<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\SignalSlot;

abstract class AbstractContentSlotTest extends AbstractSlotTest
{
    protected $contentId = 42;
    protected $locationId = null;
    protected $parentLocationId = null;

    /**
     * @return array
     */
    public function generateTags()
    {
        $tags = [];
        if ($this->contentId) {
            $tags = ['content-' . $this->contentId, 'relation-' . $this->contentId];
        }

        if ($this->locationId) {
            // self(s)
            $tags[] = 'location-' . $this->locationId;
            // children
            $tags[] = 'parent-' . $this->locationId;
        }

        if ($this->parentLocationId) {
            // parent(s)
            $tags[] = 'location-' . $this->parentLocationId;
            // siblings
            $tags[] = 'parent-' . $this->parentLocationId;
        }

        return $tags;
    }
}
