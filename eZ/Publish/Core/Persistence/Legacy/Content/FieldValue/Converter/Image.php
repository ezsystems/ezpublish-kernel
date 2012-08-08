<?php
/**
 * File containing the Image converter
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints,
    eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition,
    eZ\Publish\Core\FieldType\Image\Value as ImageValue,
    eZ\Publish\Core\FieldType\FieldSettings;

class Image implements Converter
{
    /**
     * Factory for current class
     *
     * @note Class should instead be configured as service if it gains dependencies.
     *
     * @static
     * @return Image
     */
    public static function create()
    {
        return new self;
    }

    /**
     * Converts data from $value to $storageFieldValue
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $value
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $storageFieldValue
     */
    public function toStorageValue( FieldValue $value, StorageFieldValue $storageFieldValue )
    {
        // Only store data if it is already available (after storage has stored
        // image)
        if ( isset( $value->data ) && isset( $value->data['mime'] ) )
        {
            $storageFieldValue->dataText = $this->createLegacyXml( $value->data );
        }
    }

    /**
     * Returns the XML required by the legacy database
     *
     * @param array $data
     * @return string
     */
    protected function createLegacyXml( array $data )
    {

        // <?xml version="1.0" encoding="utf-8"
        // <ezimage serial_number="1" is_valid="1" filename="River-Boat.jpg" suffix="jpg" basename="River-Boat" dirpath="var/ezdemo_site/storage/images/travel/peruvian-amazon/river-boat/322-1-eng-US" url="var/ezdemo_site/storage/images/travel/peruvian-amazon/river-boat/322-1-eng-US/River-Boat.jpg" original_filename="bbbbc2fe.jpg" mime_type="image/jpeg" width="770" height="512" alternative_text="Old River Boat" alias_key="1293033771" timestamp="1342530101">
        //   <original attribute_id="322" attribute_version="1" attribute_language="eng-US"/>
        //   <information Height="512" Width="770" IsColor="1"/>
        // </ezimage>

$xml = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<ezimage serial_number="1" is_valid="1" filename="%s"
    suffix="%s" basename="%s" dirpath="%s" url="%s"
    original_filename="%s" mime_type="%s" width="%s"
    height="%s" alternative_text="%s" alias_key="%s" timestamp="%s">
  <original attribute_id="%s" attribute_version="%s" attribute_language="%s"/>
  <information Height="%s" Width="%s" IsColor="%s"/>
</ezimage>
EOT;
        $pathInfo = pathinfo( $data['path'] );

        return sprintf(
            $xml,
            // <ezimage>
            htmlspecialchars( $pathInfo['basename'] ), // filename
            htmlspecialchars( $pathInfo['extension'] ), // suffix
            htmlspecialchars( $pathInfo['dirname'] ), // basename
            htmlspecialchars( $data['path'] ), // dirpath
            htmlspecialchars( $data['path'] ), // url
            null, // @TODO: Needs original file name, for whatever reason?
            htmlspecialchars( $data['mime'] ), // mime_type
            htmlspecialchars( $data['width'] ), // width
            htmlspecialchars( $data['height'] ), // height
            htmlspecialchars( $data['alternativeText'] ), // alternative_text
            htmlspecialchars( $timestamp = time() ), // alias_key
            htmlspecialchars( $timestamp ), // timestamp
            // <original>
            $data['fieldId'],
            $data['versionNo'],
            $data['languageCode'],
            // <information>
            $data['height'], // Height
            $data['width'], // Width
            1 // IsColor @TODO Do we need to fix that here?
        );
    }

    /**
     * Converts data from $value to $fieldValue
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue( StorageFieldValue $value, FieldValue $fieldValue )
    {
        $fieldValue->data = $this->parseLegacyXml( $value->dataText );
    }


    /**
     * Parses the XML from the legacy database
     *
     * Returns only the data required by the FieldType, nothing more.
     *
     * @param string $xml
     * @return array
     */
    protected function parseLegacyXml( $xml )
    {
        $extractedData = array();

        $dom = new \DOMDocument();
        $dom->loadXml( $xml );

        $ezimageTag = $dom->documentElement;

        if ( !$ezimageTag->hasAttribute( 'url' ) )
        {
            throw new \RuntimeException( 'Missing attribute "url" in <ezimage/> tag.' );
        }
        $extractedData['path'] = $ezimageTag->getAttribute( 'url' );

        if ( !$ezimageTag->hasAttribute( 'filename' ) )
        {
            throw new \RuntimeException( 'Missing attribute "filename" in <ezimage/> tag.' );
        }
        $extractedData['fileName'] = $ezimageTag->getAttribute( 'filename' );

        if ( !$ezimageTag->hasAttribute( 'alternative_text' ) )
        {
            throw new \RuntimeException( 'Missing attribute "alternative_text" in <ezimage/> tag.' );
        }
        $extractedData['alternativeText'] = $ezimageTag->getAttribute( 'alternative_text' );

        return $extractedData;
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition( FieldDefinition $fieldDef, StorageFieldDefinition $storageDef )
    {
        $storageDef->dataInt1 = ( isset( $fieldDef->fieldTypeConstraints->validators['FileSizeValidator']['maxFileSize'] )
            ? round( $fieldDef->fieldTypeConstraints->validators['FileSizeValidator']['maxFileSize'] / 1024 / 1024 )
            : 0 );
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition( StorageFieldDefinition $storageDef, FieldDefinition $fieldDef )
    {
        $fieldDef->fieldTypeConstraints = new FieldTypeConstraints( array(
            'validators' => array(
                'FileSizeValidator' => array(
                    'maxFileSize' => ( $storageDef->dataInt1 != 0
                        ? (int)$storageDef->dataInt1 * 1024 * 1024
                        : false ),
                )
            )
        ) );
    }

    /**
     * Returns the name of the index column in the attribute table
     *
     * Returns the name of the index column the datatype uses, which is either
     * "sort_key_int" or "sort_key_string". This column is then used for
     * filtering and sorting for this type.
     *
     * @return string
     */
    public function getIndexColumn()
    {
        // @TODO: Correct?
        return 'sort_key_string';
    }
}
