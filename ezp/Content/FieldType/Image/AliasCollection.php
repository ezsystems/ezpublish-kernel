<?php
/**
 * File containing the AliasCollection class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Image;
use ezp\Base\Collection\Type as TypeCollection,
    ezp\Content\FieldType\Image\Exception\InvalidAlias,
    ezp\Content\FieldType\Image\Exception\MissingClass;

/**
 * Image alias collection.
 * This collection can only hold image Alias objects
 */
class AliasCollection extends TypeCollection
{
    /**
     * Image type value
     *
     * @var \ezp\Content\FieldType\Image\Value
     */
    protected $imageValue;

    /**
     *
     * @var \ezp\Content\FieldType\Image\Manager
     */
    protected $imageManager;

    public function __construct( Value $imageValue, array $elements = array() )
    {
        $this->imageValue = $imageValue;
        $this->imageManager = new Manager;
        parent::__construct( 'ezp\\Content\\FieldType\\Image\\Alias', $elements );
    }

    /**
     * Returns image alias identified by $aliasName).
     * If needed, the alias will be created
     *
     * @param string $aliasName
     * @return \ezp\Content\FieldType\Image\Alias
     * @throws \ezp\Content\FieldType\Image\Exception\InvalidAlias when trying to access to an invalid (not configured) image alias
     */
    public function offsetGet( $aliasName )
    {
        if ( !$this->imageManager->hasAlias( $aliasName ) )
            throw new InvalidAlias( $aliasName );

        if ( parent::offsetExists( $aliasName ) )
            return parent::offsetGet( $aliasName );

        // "original" alias is mandatory to create a new one
        if ( parent::offsetExists( 'original' ) )
            throw new MissingAlias( 'original' );

        $alias = $this->imageManager->createImageAlias( $aliasName );
        parent::offsetSet( $aliasName, $alias );
    }
}
