<?php
/**
 * File containing the ezp\Content\Field class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content;
use ezp\Base\Observable,
    ezp\Base\Observer,
    ezp\Base\Model,
    ezp\Content\Type\Field as FieldDefinition;

/**
 * This class represents a Content's field
 *
 */
abstract class Field extends Model implements Observer
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readWriteProperties = array(
        'id' => false,
        'languageCode' => true,
        'fieldTypeString' => true,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'version' => false,
        'fieldDefinition' => false,
    );

    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var string
     */
    protected $languageCode = '';

    /**
     * @var string
     */
    protected $fieldTypeString = '';

    /**
     * @var Version
     */
    protected $version;

    /**
     * @var FieldDefinition
     */
    protected $fieldDefinition;

    /**
     * Constructor, sets up properties
     *
     * @param Version $contentVersion
     * @param FieldDefinition $fieldDefinition
     */
    public function __construct( Version $contentVersion, FieldDefinition $fieldDefinition )
    {
        $this->version = $contentVersion;
        $this->fieldDefinition = $fieldDefinition;
        $this->fieldTypeString = $fieldDefinition->fieldTypeString;
    }

    /**
     * Return content version object
     *
     * @return Version
     */
    protected function getVersion()
    {
        return $this->version;
    }

    /**
     * Return content type object
     *
     * @return FieldDefinition
     */
    protected function getFieldDefinition()
    {
        return $this->fieldDefinition;
    }

    /**
     * Called when subject has been updated
     *
     * @param Observable $subject
     * @param string $event
     * @return ContentField
     */
    public function update( Observable $subject, $event = 'update' )
    {
        if ( $subject instanceof Version )
        {
            return $this->notify( $event );
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->fieldTypeString;
    }
}

?>
