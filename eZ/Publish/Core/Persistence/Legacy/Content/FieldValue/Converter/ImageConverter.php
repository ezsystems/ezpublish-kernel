<?php

/**
 * File containing the Image converter.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;

use eZ\Publish\Core\IO\IOServiceInterface;
use eZ\Publish\Core\IO\UrlRedecoratorInterface;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;

class ImageConverter extends BinaryFileConverter
{
    /** @var \eZ\Publish\Core\IO\IOServiceInterface */
    private $imageIoService;

    /** @var \eZ\Publish\Core\IO\UrlRedecoratorInterface */
    private $urlRedecorator;

    public function __construct(IOServiceInterface $imageIoService, UrlRedecoratorInterface $urlRedecorator)
    {
        $this->imageIoService = $imageIoService;
        $this->urlRedecorator = $urlRedecorator;
    }

    /**
     * Converts data from $value to $storageFieldValue.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $value
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $storageFieldValue
     */
    public function toStorageValue(FieldValue $value, StorageFieldValue $storageFieldValue)
    {
        if (isset($value->data)) {
            // Determine what needs to be stored
            if (isset($value->data['width']) && isset($value->data['fieldId'])) {
                // width + field id set means that something really needs to be stored
                $storageFieldValue->dataText = $this->createLegacyXml($value->data);
            } elseif (isset($value->data['fieldId'])) {
                // $fieldId without width mleans an empty field
                $storageFieldValue->dataText = $this->createEmptyLegacyXml($value->data);
            }
            // otherwise the image is unprocessed and the DB field stays empty
            // there will be a subsequent call to this method, after the image
            // has been stored
        }
    }

    /**
     * Creates an XML considered "empty" by the legacy storage.
     *
     * @param array $contentMetaData
     *
     * @return string
     */
    protected function createEmptyLegacyXml($contentMetaData)
    {
        return $this->fillXml(
            array_merge(
                [
                    'uri' => '',
                    'path' => '',
                    'width' => '',
                    'height' => '',
                    'mime' => '',
                    'alternativeText' => '',
                ],
                $contentMetaData
            ),
            [
                'basename' => '',
                'extension' => '',
                'dirname' => '',
                'filename' => '',
            ],
            time()
        );
    }

    /**
     * Returns the XML required by the legacy database.
     *
     * @param array $data
     *
     * @return string
     */
    protected function createLegacyXml(array $data)
    {
        $data['uri'] = $this->urlRedecorator->redecorateFromSource($data['uri']);
        $pathInfo = pathinfo($data['uri']);

        return $this->fillXml($data, $pathInfo, time());
    }

    /**
     * Fill the XML template with the data provided.
     *
     * @param array $imageData
     * @param array $pathInfo
     * @param int $timestamp
     *
     * @return string
     */
    protected function fillXml($imageData, $pathInfo, $timestamp)
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
            ($pathInfo['basename'] !== '' ? '1' : ''), // is_valid="%s"
            htmlspecialchars($pathInfo['basename']), // filename="%s"
            htmlspecialchars($pathInfo['extension']), // suffix="%s"
            htmlspecialchars($pathInfo['filename']), // basename="%s"
            htmlspecialchars($pathInfo['dirname']), // dirpath
            htmlspecialchars($imageData['uri']), // url
            htmlspecialchars($pathInfo['basename']), // @todo: Needs original file name, for whatever reason?
            htmlspecialchars($imageData['mime']), // mime_type
            htmlspecialchars($imageData['width']), // width
            htmlspecialchars($imageData['height']), // height
            htmlspecialchars($imageData['alternativeText']), // alternative_text
            htmlspecialchars(1293033771), // alias_key, fixed for the original image
            htmlspecialchars($timestamp), // timestamp
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
     * Converts data from $value to $fieldValue.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue(StorageFieldValue $value, FieldValue $fieldValue)
    {
        if (empty($value->dataText)) {
            // Special case for anonymous user
            return;
        }
        $fieldValue->data = $this->parseLegacyXml($value->dataText);
    }

    /**
     * Parses the XML from the legacy database.
     *
     * Returns only the data required by the FieldType, nothing more.
     *
     * @param string $xml
     *
     * @return array
     */
    protected function parseLegacyXml($xml)
    {
        $extractedData = [];

        $dom = new \DOMDocument();
        $dom->loadXml($xml);

        $ezimageTag = $dom->documentElement;

        if (!$ezimageTag->hasAttribute('url')) {
            throw new \RuntimeException('Missing attribute "url" in <ezimage/> tag.');
        }

        if (($legacyUrl = $ezimageTag->getAttribute('url')) === '') {
            // Detected XML considered "empty" by the legacy storage
            return null;
        }

        $url = $this->urlRedecorator->redecorateFromTarget($legacyUrl);
        $extractedData['id'] = $this->imageIoService->loadBinaryFileByUri($url)->id;

        if (!$ezimageTag->hasAttribute('filename')) {
            throw new \RuntimeException('Missing attribute "filename" in <ezimage/> tag.');
        }
        $extractedData['fileName'] = $ezimageTag->getAttribute('filename');
        $extractedData['width'] = $ezimageTag->getAttribute('width');
        $extractedData['height'] = $ezimageTag->getAttribute('height');
        $extractedData['mime'] = $ezimageTag->getAttribute('mime_type');

        if (!$ezimageTag->hasAttribute('alternative_text')) {
            throw new \RuntimeException('Missing attribute "alternative_text" in <ezimage/> tag.');
        }
        $extractedData['alternativeText'] = $ezimageTag->getAttribute('alternative_text');

        return $extractedData;
    }
}
