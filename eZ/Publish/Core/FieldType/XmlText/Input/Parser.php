<?php
/**
 * File containing the eZ\Publish\Core\FieldType\XmlText\Input\Parser interface.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText\Input;

use eZ\Publish\Core\FieldType\XmlText\Input\Handler;

/**
 * XmlText input parser interface
 */
interface Parser
{
    /**
     * Processes $xmlString
     *
     * @param string $xmlString
     * @param bool $createRootNode
     *
     * @return \DOMDocument
     */
    public function process( $xmlString, $createRootNode = true );

    /**
     * Returns the XML processing messages
     * @return array
     */
    public function getMessages();

    /**
     * Returns the validity status of the processed XML String
     * @return bool
     */
    public function isValid();

    /**
     * Sets the input handler for the parser to $handler
     * @param \eZ\Publish\Core\FieldType\XmlText\Input\Handler $handler
     */
    public function setHandler( Handler $handler );

    /**
     * Sets the parser option $option to $value
     * @param string $option One of self::OPT_*
     * @param mixed $value
     * @throws \eZ\Publish\Core\Base\Exceptions\BadConfiguration If the option is unknown or the value incorrect
     */
    public function setOption( $option, $value );

    /**
     * Gets the parser option $option
     * @param string $option One of self::OPT_*
     * @throws \eZ\Publish\Core\Base\Exceptions\BadConfiguration If the option is unknown or the value incorrect
     */
    public function getOption( $option );

    /**
     * @return array
     */
    public function getRelatedContentIdArray();

    /**
     * @return array
     */
    public function getLinkedContentIdArray();

    /**
     * @return array
     */
    public function getUrlIdArray();
}
