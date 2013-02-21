<?php
/**
 * File containing the Content Search FieldNameGenerator class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search;

use eZ\Publish\SPI\Persistence\Content\Search\FieldType;

/**
 * Generator for Solr field names
 */
class FieldNameGenerator
{
    /**
     * Simple mapping for our internal field types
     *
     * We implement this mapping, because those dynamic fields are common to
     * Solr configurations.
     *
     * @var array
     */
    protected $fieldNameMapping = array(
        "ez_integer"  => "i",
        "ez_id"       => "id",
        "ez_mid"      => "mid",
        "ez_string"   => "s",
        "ez_long"     => "l",
        "ez_text"     => "t",
        "ez_html"     => "h",
        "ez_boolean"  => "b",
        "ez_float"    => "f",
        "ez_double"   => "d",
        "ez_date"     => "dt",
        "ez_point"    => "p",
        "ez_currency" => "c",
    );

    /**
     * Get name for Solr document field
     *
     * Consists of a name, and optionally field anem and a content type name.
     *
     * @param string $name
     * @param string $field
     * @param string $type
     *
     * @return string
     */
    public function getName( $name, $field = null, $type = null )
    {
        return implode( '/', array_filter( array( $type, $field, $name ) ) );
    }

    /**
     * Map field type
     *
     * For Solr indexing the following scheme will always be used for names:
     * {name}_{type}.
     *
     * Using dynamic fields this allows to define fields either depending on
     * types, or names.
     *
     * Only the field with the name ID remains untouched.
     *
     * @param string $name
     * @param FieldType $type
     *
     * @return string
     */
    public function getTypedName( $name, FieldType $type )
    {
        if ( $name === "id" )
        {
            return $name;
        }

        $typeName = $type->type;
        if ( isset( $this->fieldNameMapping[$typeName] ) )
        {
            $typeName = $this->fieldNameMapping[$typeName];
        }

        return $name . '_' . $typeName;
    }
}

