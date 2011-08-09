<?php
/**
 * File containing the ezp\Content\Field class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content;
use ezp\Base\Model,
    ezp\Content\Version,
    ezp\Content\Type\FieldDefinition,
    ezp\Persistence\Content\Field as FieldVO;

/**
 * This class represents a Content's field
 *
 * @property-read mixed $id
 * @property-ready string $type
 * @property-read FieldValue $value
 * @property string $type
 * @property mixed $language
 * @property-read int $versionNo
 * @property-read \ezp\Content\Version $version
 * @property-read \ezp\Content\Type\FieldDefinition $fieldDefinition
 */
class Field extends Model
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readWriteProperties = array(
        'id' => false,
        'type' => false,
        'value' => true,
        'language' => true,
        'versionNo' => false,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'version' => false,
        'fieldDefinition' => false,
    );

    /**
     * @var \ezp\Content\Version
     */
    protected $_version;

    /**
     * @var \ezp\Content\Type\FieldDefinition
     */
    protected $_fieldDefinition;

    /**
     * Constructor, sets up properties
     *
     * @param \ezp\Content\Version $contentVersion
     * @param \ezp\Content\Type\FieldDefinition $fieldDefinition
     */
    public function __construct( Version $contentVersion, FieldDefinition $fieldDefinition )
    {
        $this->_version = $contentVersion;
        $this->_fieldDefinition = $fieldDefinition;
        $this->properties = new FieldVO( array(
                                               'type' => $fieldDefinition->fieldType,
                                               'fieldDefinitionId' => $fieldDefinition->id,
                                               'value' => $fieldDefinition->defaultValue,
                                           ) );
    }

    /**
     * Return content version object
     *
     * @return \ezp\Content\Version
     */
    protected function getVersion()
    {
        return $this->_version;
    }

    /**
     * Return content type object
     *
     * @return \ezp\Content\Type\FieldDefinition
     */
    protected function getFieldDefinition()
    {
        return $this->_fieldDefinition;
    }
}

?>
