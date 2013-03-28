<?php
/**
 * File containing the Image converter
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;

use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;

class Image implements Converter
{
    /**
     * Factory for current class
     *
     * @note Class should instead be configured as service if it gains dependencies.
     *
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
        if ( isset( $value->data ) )
        {
            // Determine what needs to be stored
            if ( isset( $value->data['mime'] ) )
            {
                // $data['mime'] is only set for real images, which have been
                // stored
                $storageFieldValue->dataText = $this->createLegacyXml( $value->data );
            }
            else if ( isset( $value->data['fieldId'] ) )
            {
                // $fieldId is only set if data is to be stored at all
                $storageFieldValue->dataText = $this->createEmptyLegacyXml( $value->data );
            }
            // otherwise the image is unprocessed and the DB field stays empty
            // there will be a subsequent call to this method, after the image
            // has been stored
        }
    }

    /**
     * Creates an XML considered "empty" by the legacy storage
     *
     * @param array $contentMetaData
     *
     * @return string
     */
    protected function createEmptyLegacyXml( $contentMetaData )
    {
        return $this->fillXml(
            array_merge(
                array(
                    'path' => '',
                    'width' => '',
                    'height' => '',
                    'mime' => '',
                    'alternativeText' => '',
                ),
                $contentMetaData
            ),
            array(
                'basename' => '',
                'extension' => '',
                'dirname' => '',
                'filename' => '',
            ),
            time()
        );
    }

    /**
     * Returns the XML required by the legacy database
     *
     * @param array $data
     *
     * @return string
     */
    protected function createLegacyXml( array $data )
    {
        $pathInfo = pathinfo( $data['path'] );
        return $this->fillXml( $data, $pathInfo, time() );
    }

    /**
     * Fill the XML template with the data provided
     *
     * @param array $imageData
     * @param array $pathInfo
     * @param int $timestamp
     *
     * @return string
     */
    protected function fillXml( $imageData, $pathInfo, $timestamp )
    {
        // <?xml version="1.0" encoding="utf-8"
        // <ezimage serial_number="1" is_valid="1" filename="River-Boat.jpg" suffix="jpg" basename="River-Boat" dirpath="var/ezdemo_site/storage/images/travel/peruvian-amazon/river-boat/322-1-eng-US" url="var/ezdemo_site/storage/images/travel/peruvian-amazon/river-boat/322-1-eng-US/River-Boat.jpg" original_filename="bbbbc2fe.jpg" mime_type="image/jpeg" width="770" height="512" alternative_text="Old River Boat" alias_key="1293033771" timestamp="1342530101">
        //   <original attribute_id="322" attribute_version="1" attribute_language="eng-US"/>
        //   <information Height="512" Width="770" IsColor="1"/>
        // </ezimage>
$xml = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<ezimage serial_number="1" is_valid="%s" filename="%s"
    suffix="%s" basename="%s" dirpath="%s" url="%s"
    original_filename="%s" mime_type="%s" width="%s"
    height="%s" alternative_text="%s" alias_key="%s" timestamp="%s">
  <original attribute_id="%s" attribute_version="%s" attribute_language="%s"/>
  <information Height="%s" Width="%s" IsColor="%s"/>
</ezimage>
EOT;

        return sprintf(
            $xml,
            // <ezimage>
            ( $pathInfo['basename'] !== '' ? '1' : '' ), // is_valid="%s"
            htmlspecialchars( $pathInfo['basename'] ), // filename="%s"
            htmlspecialchars( $pathInfo['extension'] ), // suffix="%s"
            htmlspecialchars( $pathInfo['filename'] ), // basename="%s"
            htmlspecialchars( $pathInfo['dirname'] ), // dirpath
            htmlspecialchars( $imageData['path'] ), // url
            htmlspecialchars( $pathInfo['basename'] ), // @todo: Needs original file name, for whatever reason?
            htmlspecialchars( $imageData['mime'] ), // mime_type
            htmlspecialchars( $imageData['width'] ), // width
            htmlspecialchars( $imageData['height'] ), // height
            htmlspecialchars( $imageData['alternativeText'] ), // alternative_text
            htmlspecialchars( 1293033771 ), // alias_key, fixed for the original image
            htmlspecialchars( $timestamp ), // timestamp
            // <original>
            $imageData['fieldId'],
            $imageData['versionNo'],
            $imageData['languageCode'],
            // <information>
            $imageData['height'], // Height
            $imageData['width'], // Width
            1 // IsColor @todo Do we need to fix that here?
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
        if ( empty( $value->dataText ) )
        {
            // Special case for anonymous user
            return;
        }
        $fieldValue->data = $this->parseLegacyXml( $value->dataText );
    }

    /**
     * Parses the XML from the legacy database
     *
     * Returns only the data required by the FieldType, nothing more.
     *
     * @param string $xml
     *
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

        if ( ( $url = $ezimageTag->getAttribute( 'url' ) ) === '' )
        {
            // Detected XML considered "empty" by the legacy storage
            return null;
        }

        $extractedData['path'] = $url;

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
        $fieldDef->fieldTypeConstraints = new FieldTypeConstraints(
            array(
                'validators' => array(
                    'FileSizeValidator' => array(
                        'maxFileSize' => ( $storageDef->dataInt1 != 0
                            ? (int)$storageDef->dataInt1 * 1024 * 1024
                            : false ),
                    )
                )
            )
        );
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
        // @todo: Correct?
        return 'sort_key_string';
    }
}
