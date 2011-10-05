<?php
/**
 * File containing the image Manager class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Image;
use ezp\Base\Legacy\Carpet;

/**
 * Wraps eZImageManager class from old eZ Publish
 *
 * @note This implementation is to be changed not to be dependent on the old eZImageManager
 */
class Manager extends Carpet
{
    protected static $className = 'eZImageManager';

    public function __construct()
    {
        parent::__construct( static::$className );
    }

    public function createImageAlias( $aliasName )
    {
        $alias = new Alias;
    }
}
