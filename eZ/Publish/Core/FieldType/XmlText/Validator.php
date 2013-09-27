<?php
/**
 * File containing the eZ\Publish\Core\FieldType\XmlText\Validator class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use DOMDocument;
use LibXMLError;

/**
 * Validates XML document using XSD or RELAX NG schema.
 */
class Validator
{
    /**
     *
     *
     * @var string
     */
    protected $schema;

    /**
     * Textual mapping for libxml error types.
     *
     * @var array
     */
    protected $errorTypes = array(
        LIBXML_ERR_WARNING => 'Warning',
        LIBXML_ERR_ERROR => 'Error',
        LIBXML_ERR_FATAL => 'Fatal error',
    );

    /**
     * Constructor @todo
     *
     * @param string $schema Schema to use for validation
     */
    public function __construct( $schema )
    {
        $this->schema = $schema;
    }

    /**
     * @param LibXMLError $error
     *
     * @return string
     */
    protected function formatLibXmlError( LibXMLError $error )
    {
        return sprintf(
            "%s in %d:%d: %s",
            $this->errorTypes[$error->level],
            $error->line,
            $error->column,
            trim( $error->message )
        );
    }

    /**
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     *
     * @param \DOMDocument $document
     *
     * @return string[]
     */
    public function validate( DOMDocument $document )
    {
        if ( !file_exists( $this->schema ) || !is_file( $this->schema ) )
        {
            throw new InvalidArgumentException(
                "schemaPath",
                "Conversion of XML document cannot be performed, file '{$this->schema}' does not exist."
            );
        }

        $oldSetting = libxml_use_internal_errors( true );
        libxml_clear_errors();

        $pathInfo = pathinfo( $this->schema );
        switch ( $pathInfo["extension"] )
        {
            case "xsd";
                $document->schemaValidate( $this->schema );
                break;
            case "rng";
                $document->relaxNGValidate( $this->schema );
                break;
            default:
                throw new InvalidArgumentException(
                    "schemaPath",
                    "Validator is capable of handling XSD and RELAX NG schema files, ending in .xsd or .rng." .
                    "File '{$this->schema}' does not seem to be either of these."
                );
        }

        // Get all errors
        $xmlErrors = libxml_get_errors();
        $errors = array();
        foreach ( $xmlErrors as $error )
        {
            $errors[] = $this->formatLibXmlError( $error );
        }
        libxml_clear_errors();
        libxml_use_internal_errors( $oldSetting );

        return $errors;
    }
}
