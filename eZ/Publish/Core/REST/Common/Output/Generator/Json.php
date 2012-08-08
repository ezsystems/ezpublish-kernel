<?php
/**
 * File containing the Json generator class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Generator;

/**
 * Json generator
 */
class Json extends Generator
{
    /**
     * Data structure which is build during visiting;
     *
     * @var array
     */
    protected $json;

    /**
     * Start document
     *
     * @param mixed $data
     */
    public function startDocument( $data )
    {
        $this->checkStartDocument( $data );

        $this->json = new Json\Object();
    }

    /**
     * End document
     *
     * Returns the generated document as a string.
     *
     * @param mixed $data
     * @return string
     */
    public function endDocument( $data )
    {
        $this->checkEndDocument( $data );

        $this->json = $this->convertArrayObjects( $this->json );
        return json_encode( $this->json );
    }

    /**
     * Convert ArrayObjects to arrays
     *
     * Recursively convert all ArrayObjects into arrays in the full data
     * structure.
     *
     * @param mixed $data
     * @return mixed
     */
    protected function convertArrayObjects( $data )
    {
        if ( $data instanceof Json\ArrayObject )
        {
            // @TODO: Check if we need to convert arrays with only one single
            // element into non-arrays /cc cba
            $data = $data->getArrayCopy();
            foreach ( $data as $key => $value )
            {
                $data[$key] = $this->convertArrayObjects( $value );
            }
        }
        elseif ( $data instanceof Json\Object )
        {
            foreach ( $data as $key => $value )
            {
                $data->$key = $this->convertArrayObjects( $value );
            }
        }

        return $data;
    }

    /**
     * Start object element
     *
     * @param string $name
     * @param string $mediaTypeName
     */
    public function startObjectElement( $name, $mediaTypeName = null )
    {
        $this->checkStartObjectElement( $name );

        $mediaTypeName = $mediaTypeName ?: $name;

        $object = new Json\Object( $this->json );

        if ( $this->json instanceof Json\ArrayObject )
        {
            $this->json[] = $object;
            $this->json = $object;
        }
        else
        {
            $this->json->$name = $object;
            $this->json = $object;
        }

        $this->startAttribute( "media-type", $this->getMediaType( $mediaTypeName ) );
        $this->endAttribute( "media-type" );
    }

    /**
     * End object element
     *
     * @param string $name
     */
    public function endObjectElement( $name )
    {
        $this->checkEndObjectElement( $name );

        $this->json = $this->json->getParent();
    }

    /**
     * Start hash element
     *
     * @param string $name
     */
    public function startHashElement( $name )
    {
        $this->checkStartHashElement( $name );

        $object = new Json\Object( $this->json );

        if ( $this->json instanceof Json\ArrayObject )
        {
            $this->json[] = $object;
            $this->json = $object;
        }
        else
        {
            $this->json->$name = $object;
            $this->json = $object;
        }
    }

    /**
     * End hash element
     *
     * @param string $name
     */
    public function endHashElement( $name )
    {
        $this->checkEndHashElement( $name );

        $this->json = $this->json->getParent();
    }

    /**
     * Start value element
     *
     * @param string $name
     * @param string $value
     */
    public function startValueElement( $name, $value )
    {
        $this->checkStartValueElement( $name );

        $this->json->$name = $value;
    }

    /**
     * End value element
     *
     * @param string $name
     */
    public function endValueElement( $name )
    {
        $this->checkEndValueElement( $name );
    }

    /**
     * Start hash value element
     *
     * @param string $name
     * @param string $value
     * @param array $attributes
     */
    public function startHashValueElement( $name, $value, $attributes = array() )
    {
        $this->checkStartHashValueElement( $name );

        $object = new Json\Object( $this->json );
        foreach ( $attributes as $attributeName => $attributeValue )
        {
            $object->{'_' . $attributeName} = $attributeValue;
        }
        $object->{'#text'} = $value;

        if ( !isset( $this->json->$name ) )
            $this->json->$name = new Json\ArrayObject( $this->json->getParent() );

        $this->json->$name->append( $object );
    }

    /**
     * End hash value element
     *
     * @param string $name
     */
    public function endHashValueElement( $name )
    {
        $this->checkEndHashValueElement( $name );
    }

    /**
     * Start list
     *
     * @param string $name
     */
    public function startList( $name )
    {
        $this->checkStartList( $name );

        $array = new Json\ArrayObject( $this->json );

        $this->json->$name = $array;
        $this->json = $array;
    }

    /**
     * End list
     *
     * @param string $name
     */
    public function endList( $name )
    {
        $this->checkEndList( $name );

        $this->json = $this->json->getParent();
    }

    /**
     * Start attribute
     *
     * @param string $name
     * @param string $value
     */
    public function startAttribute( $name, $value )
    {
        $this->checkStartAttribute( $name );

        $this->json->{'_' . $name} = $value;
    }

    /**
     * End attribute
     *
     * @param string $name
     */
    public function endAttribute( $name )
    {
        $this->checkEndAttribute( $name );
    }

    /**
     * Get media type
     *
     * @param string $name
     * @return string
     */
    public function getMediaType( $name )
    {
        return $this->generateMediaType( $name, 'json' );
    }
}
