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
 */
class Manager extends Carpet
{
    public function __construct()
    {
        parent::__construct( 'eZImageManager' );
    }

    public function createImageAlias( $aliasName )
    {
        $alias = new Alias;
    }
}
