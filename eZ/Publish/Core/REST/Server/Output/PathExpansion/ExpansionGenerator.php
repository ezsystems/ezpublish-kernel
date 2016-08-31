<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\PathExpansion;

use eZ\Publish\Core\Base\Exceptions\BadStateException;
use eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException;
use eZ\Publish\Core\REST\Common\Output\Generator;

/**
 * A REST output generator meant to expand an existing generator.
 *
 * @todo a crappy description for a crappy class name. It all makes sense. Or it will.
 */
class ExpansionGenerator extends Generator
{
    /**
     * @var Generator
     */
    private $innerGenerator;

    /**
     * ExpansionGenerator constructor.
     * @param Generator $generator
     */
    public function __construct(Generator $generator)
    {
        $this->innerGenerator = $generator;
    }

    /**
     * Not supported in this generator, as the context is always within a generated document.
     *
     * @param mixed $data
     *
     * @throws BadStateException
     */
    public function startDocument($data)
    {
        throw new BadStateException(
            'generator',
            'start/endDocument can not be used with the ExpansionGenerator'
        );
    }

    /**
     * Returns if the document is empty or already contains data.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->innerGenerator->isEmpty();
    }

    /**
     * Not supported in this generator, as the context is always within a generated document.
     *
     * @param mixed $data
     *
     * @throws BadStateException
     */
    public function endDocument($data)
    {
        throw new BadStateException('generator', 'start/endDocument can not be used with the ExpansionGenerator');
    }

    /**
     * Start object element.
     * If the element is the first added to this generator, it is silently skipped.
     * Subsequent elements are started as expected.
     *
     * @param string $name
     * @param string $mediaTypeName
     */
    public function startObjectElement($name, $mediaTypeName = null)
    {
        $this->stack[] = $name;

        if (count($this->stack) > 1) {
            $this->innerGenerator->startObjectElement($name, $mediaTypeName);
        }
    }

    /**
     * End object element.
     *
     * @param string $name
     */
    public function endObjectElement($name)
    {
        $objectElementName = array_pop($this->stack);
        if (count($this->stack) > 0) {
            $this->innerGenerator->endObjectElement($name);
        } else {
            if ($name !== $objectElementName) {
                throw new OutputGeneratorException("Closing object element name doesn't match the opening one ($objectElementName)");
            }
        }
    }

    /**
     * Start hash element.
     *
     * @param string $name
     */
    public function startHashElement($name)
    {
        $this->innerGenerator->startHashElement($name);
    }

    /**
     * End hash element.
     *
     * @param string $name
     */
    public function endHashElement($name)
    {
        $this->innerGenerator->endHashElement($name);
    }

    /**
     * Start value element.
     *
     * @param string $name
     * @param string $value
     */
    public function startValueElement($name, $value)
    {
        $this->innerGenerator->startValueElement($name, $value);
    }

    /**
     * End value element.
     *
     * @param string $name
     */
    public function endValueElement($name)
    {
        $this->innerGenerator->endValueElement($name);
    }

    /**
     * Start list.
     *
     * @param string $name
     */
    public function startList($name)
    {
        $this->innerGenerator->startList($name);
    }

    /**
     * End list.
     *
     * @param string $name
     */
    public function endList($name)
    {
        $this->innerGenerator->endList($name);
    }

    /**
     * Start attribute.
     * Skips the href and media-type attribute at stack depth 0.
     *
     * @param string $name
     * @param string $value
     */
    public function startAttribute($name, $value)
    {
        if (!in_array($name, ['href', 'media-type']) || count($this->stack) > 1) {
            $this->innerGenerator->startAttribute($name, $value);
        }
    }

    /**
     * End attribute.
     *
     * @param string $name
     */
    public function endAttribute($name)
    {
        if (!in_array($name, ['href', 'media-type']) || count($this->stack) > 1) {
            $this->innerGenerator->endAttribute($name);
        }
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
        return $this->innerGenerator->getMediaType($name);
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
        $this->innerGenerator->generateFieldTypeHash($hashElementName, $hashValue);
    }

    /**
     * Serializes a boolean value.
     *
     * @param bool $boolValue
     *
     * @return mixed
     */
    public function serializeBool($boolValue)
    {
        return $this->innerGenerator->serializeBool($boolValue);
    }

    public function getStackPath()
    {
        return $this->innerGenerator->getStackPath();
    }
}
