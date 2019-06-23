<?php

/**
 * File containing the Json generator class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\Output\Generator;

use eZ\Publish\Core\REST\Common\Output\Generator;

/**
 * Json generator.
 */
class Json extends Generator
{
    /**
     * Data structure which is build during visiting;.
     *
     * @var array
     */
    protected $json;

    /**
     * Generator for field type hash values.
     *
     * @var \eZ\Publish\Core\REST\Common\Output\Generator\Json\FieldTypeHashGenerator
     */
    protected $fieldTypeHashGenerator;

    /**
     * Keeps track if the document is still empty.
     *
     * @var bool
     */
    protected $isEmpty = true;

    /**
     * Enables developer to modify REST response media type prefix.
     *
     * @var string
     */
    protected $vendor;

    /**
     * @param \eZ\Publish\Core\REST\Common\Output\Generator\Json\FieldTypeHashGenerator $fieldTypeHashGenerator
     * @param string $vendor
     */
    public function __construct(Json\FieldTypeHashGenerator $fieldTypeHashGenerator, $vendor = 'vnd.ez.api')
    {
        $this->fieldTypeHashGenerator = $fieldTypeHashGenerator;
        $this->vendor = $vendor;
    }

    /**
     * Start document.
     *
     * @param mixed $data
     */
    public function startDocument($data)
    {
        $this->checkStartDocument($data);

        $this->isEmpty = true;

        $this->json = new Json\JsonObject();
    }

    /**
     * Returns if the document is empty or already contains data.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->isEmpty;
    }

    /**
     * End document.
     *
     * Returns the generated document as a string.
     *
     * @param mixed $data
     *
     * @return string
     */
    public function endDocument($data)
    {
        $this->checkEndDocument($data);

        $jsonEncodeOptions = 0;
        if ($this->formatOutput && defined('JSON_PRETTY_PRINT')) {
            $jsonEncodeOptions = JSON_PRETTY_PRINT;
        }

        $this->json = $this->convertArrayObjects($this->json);

        return json_encode($this->json, $jsonEncodeOptions);
    }

    /**
     * Convert ArrayObjects to arrays.
     *
     * Recursively convert all ArrayObjects into arrays in the full data
     * structure.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    protected function convertArrayObjects($data)
    {
        if ($data instanceof Json\ArrayObject) {
            // @todo: Check if we need to convert arrays with only one single
            // element into non-arrays /cc cba
            $data = $data->getArrayCopy();
            foreach ($data as $key => $value) {
                $data[$key] = $this->convertArrayObjects($value);
            }
        } elseif ($data instanceof Json\JsonObject) {
            foreach ($data as $key => $value) {
                $data->$key = $this->convertArrayObjects($value);
            }
        }

        return $data;
    }

    /**
     * Start object element.
     *
     * @param string $name
     * @param string $mediaTypeName
     */
    public function startObjectElement($name, $mediaTypeName = null)
    {
        $this->checkStartObjectElement($name);

        $this->isEmpty = false;

        $mediaTypeName = $mediaTypeName ?: $name;

        $object = new Json\JsonObject($this->json);

        if ($this->json instanceof Json\ArrayObject) {
            $this->json[] = $object;
            $this->json = $object;
        } else {
            $this->json->$name = $object;
            $this->json = $object;
        }

        $this->startAttribute('media-type', $this->getMediaType($mediaTypeName));
        $this->endAttribute('media-type');
    }

    /**
     * End object element.
     *
     * @param string $name
     */
    public function endObjectElement($name)
    {
        $this->checkEndObjectElement($name);

        $this->json = $this->json->getParent();
    }

    /**
     * Start hash element.
     *
     * @param string $name
     */
    public function startHashElement($name)
    {
        $this->checkStartHashElement($name);

        $this->isEmpty = false;

        $object = new Json\JsonObject($this->json);

        if ($this->json instanceof Json\ArrayObject) {
            $this->json[] = $object;
            $this->json = $object;
        } else {
            $this->json->$name = $object;
            $this->json = $object;
        }
    }

    /**
     * End hash element.
     *
     * @param string $name
     */
    public function endHashElement($name)
    {
        $this->checkEndHashElement($name);

        $this->json = $this->json->getParent();
    }

    /**
     * Start value element.
     *
     * @param string $name
     * @param string $value
     * @param array $attributes
     */
    public function startValueElement($name, $value, $attributes = [])
    {
        $this->checkStartValueElement($name);

        $jsonValue = null;

        if (empty($attributes)) {
            $jsonValue = $value;
        } else {
            $jsonValue = new Json\JsonObject($this->json);
            foreach ($attributes as $attributeName => $attributeValue) {
                $jsonValue->{'_' . $attributeName} = $attributeValue;
            }
            $jsonValue->{'#text'} = $value;
        }

        if ($this->json instanceof Json\ArrayObject) {
            $this->json[] = $jsonValue;
        } else {
            $this->json->$name = $jsonValue;
        }
    }

    /**
     * End value element.
     *
     * @param string $name
     */
    public function endValueElement($name)
    {
        $this->checkEndValueElement($name);
    }

    /**
     * Start list.
     *
     * @param string $name
     */
    public function startList($name)
    {
        $this->checkStartList($name);

        $array = new Json\ArrayObject($this->json);

        $this->json->$name = $array;
        $this->json = $array;
    }

    /**
     * End list.
     *
     * @param string $name
     */
    public function endList($name)
    {
        $this->checkEndList($name);

        $this->json = $this->json->getParent();
    }

    /**
     * Start attribute.
     *
     * @param string $name
     * @param string $value
     */
    public function startAttribute($name, $value)
    {
        $this->checkStartAttribute($name);

        $this->json->{'_' . $name} = $value;
    }

    /**
     * End attribute.
     *
     * @param string $name
     */
    public function endAttribute($name)
    {
        $this->checkEndAttribute($name);
    }

    /**
     * Get media type.
     *
     * @param string $name
     *
     * @return string
     */
    public function getMediaType($name)
    {
        return $this->generateMediaTypeWithVendor($name, 'json', $this->vendor);
    }

    /**
     * Generates a generic representation of the scalar, hash or list given in
     * $hashValue into the document, using an element of $hashElementName as
     * its parent.
     *
     * @param string $hashElementName
     * @param mixed $hashValue
     */
    public function generateFieldTypeHash($hashElementName, $hashValue)
    {
        $this->fieldTypeHashGenerator->generateHashValue(
            $this->json,
            $hashElementName,
            $hashValue
        );
    }

    /**
     * Serializes a boolean value.
     *
     * @param bool $boolValue
     *
     * @return bool
     */
    public function serializeBool($boolValue)
    {
        return (bool)$boolValue;
    }
}
