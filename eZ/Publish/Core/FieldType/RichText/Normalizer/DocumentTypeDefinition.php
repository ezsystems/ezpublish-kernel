<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\RichText\Normalizer;

use eZ\Publish\Core\FieldType\RichText\Normalizer;

/**
 * Character entity definition normalizer adds DTD containing character entity definition to
 * string input that conforms to an XML document with configured document element and default
 * namespace.
 *
 * Note: if input already contains DTD it won't be accepted for normalization.
 *
 * @deprecated since 7.4, use \EzSystems\EzPlatformRichText\eZ\RichText\Normalizer\DocumentTypeDefinition from EzPlatformRichTextBundle.
 */
class DocumentTypeDefinition extends Normalizer
{
    /**
     * Holds root element name of the accepted XML format.
     *
     * @var string
     */
    private $documentElement;

    /**
     * Holds default namespace name of the accepted XML format.
     *
     * @var string
     */
    private $namespace;

    /**
     * Holds path to the DTD file.
     *
     * @var string
     */
    private $dtdPath;

    /**
     * Holds computed regular expression pattern for matching and replacement.
     *
     * @var string
     */
    private $expression;

    public function __construct($documentElement, $namespace, $dtdPath)
    {
        $this->documentElement = $documentElement;
        $this->namespace = $namespace;
        $this->dtdPath = $dtdPath;
    }

    /**
     * Accept if $input looks like XML document, with configured document element
     * and default namespace, but without DTD.
     *
     * @param string $input
     *
     * @return bool
     */
    public function accept($input)
    {
        if (preg_match($this->getExpression(), $input, $matches)) {
            return true;
        }

        return false;
    }

    /**
     * Normalizes given $input by adding DTD with character entity definition.
     *
     * @param string $input
     *
     * @return string
     */
    public function normalize($input)
    {
        return preg_replace(
            $this->getExpression(),
            "\${1}\n" . file_get_contents($this->dtdPath) . '${3}',
            $input
        );
    }

    /**
     * Computes and returns regular expression pattern for matching and replacement.
     *
     * @return string
     */
    private function getExpression()
    {
        if ($this->expression === null) {
            $this->expression =
                '/(<\?xml.*\?>)?([ \t\n\r]*)(<' .
                preg_quote($this->documentElement, '/') .
                '.*xmlns="' .
                preg_quote($this->namespace, '/') .
                '".*>)/is';
        }

        return $this->expression;
    }
}
