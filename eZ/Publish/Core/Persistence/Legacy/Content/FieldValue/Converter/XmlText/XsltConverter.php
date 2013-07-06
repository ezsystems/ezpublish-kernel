<?php
/**
 * File containing the eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\XmlText\XsltConverter class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\XmlText;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use DOMDocument;
use XSLTProcessor;

/**
 *
 */
abstract class XsltConverter
{
    /**
     * Path to stylesheet to use
     *
     * @var string
     */
    protected $stylesheet;

    /**
     * Constructor
     *
     * @param string $stylesheet Stylesheet to use for conversion
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     */
    public function __construct( $stylesheet )
    {
        $this->stylesheet = $stylesheet;
    }

    /**
     * Convert $xmlDoc from internal representation DOMDocument to HTML5
     *
     * @param \DOMDocument $xmlDoc
     *
     * @return string
     */
    public function convert( DOMDocument $xmlDoc )
    {
        $xslDoc = new DOMDocument;
        $xslDoc->load( $this->stylesheet );
        $xsl = new XSLTProcessor();
        $xsl->importStyleSheet( $xslDoc );

        return $xsl->transformToXML( $xmlDoc );
    }
}
