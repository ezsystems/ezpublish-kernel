<?php
/**
 * File contains Content Type Field (content class attribute) class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Type;
use ezp\Base\Observable,
    ezp\Base\Observer,
    ezp\Base\AbstractModel,
    ezp\Content\Type,
    ezp\Content\Type\Field as FieldDefinition;

/**
 * Content Type Field (content class attribute) class
 *
 * @property-read string $fieldTypeString
 */
abstract class Field extends AbstractModel implements Observer
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readableProperties = array(
        'id' => false,
        'version' => false,
        'placement' => true,
        'identifier' => true,
        'fieldTypeString' => true,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'contentType' => false,
    );

    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $version;

    /**
     * @var int
     */
    public $placement;

    /**
     * @var string
     */
    public $identifier;

    /**
     * @var string
     */
    public $fieldTypeString;

    /**
     * @var Type
     */
    protected $contentType;

    /**
     * Constructor, sets up empty contentFields collection and attach $contentType
     *
     * @param Type $contentType
     */
    public function __construct( Type $contentType )
    {
        $this->contentType = $contentType;
    }

    /**
     * Return content type object
     *
     * @return Type
     */
    protected function getContentType()
    {
        if ( $this->contentType instanceof Proxy )
        {
            $this->contentType = $this->contentType->load();
        }
        return $this->contentType;
    }

    /**
     * Called when subject has been updated
     *
     * @param ezp\Base\Observable $subject
     * @param string $event
     * @return Field
     */
    public function update( Observable $subject, $event = 'update' )
    {
        if ( $subject instanceof Type )
        {
            return $this->notify( $event );
        }
        return parent::update( $subject, $event );
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->identifier;
    }
}
