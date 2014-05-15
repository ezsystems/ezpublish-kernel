<?php
/**
 * File containing the ObjectConverter interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Templating\Converter;

/**
 * Interface for object converters.
 * The purpose of an object converter is to make objects compatible to eZ Publish legacy templates.
 */
interface ObjectConverter
{
    /**
     * Converts $object to make it compatible with eZTemplate API.
     *
     * @param object $object
     *
     * @throws \InvalidArgumentException If $object is actually not an object
     *
     * @return mixed|\eZ\Publish\Core\MVC\Legacy\Templating\LegacyCompatible
     */
    public function convert( $object );
}
