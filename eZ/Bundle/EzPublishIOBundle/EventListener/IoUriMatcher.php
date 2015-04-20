<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\EventListener;

interface IoUriMatcher
{
    /**
     * Tests if $uri is a match or not
     * @param $uri
     * @return bool
     */
    public function matches( $uri );
}
