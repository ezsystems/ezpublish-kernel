<?php
/**
 * File containing the ezp\Content\FieldType\XmlText\Input\Parser interface.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\XmlText\Input;

use ezp\Content\FieldType\XmlText\Input\Handler;

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
     * @param \ezp\Content\FieldType\XmlText\Input\Handler $handler
     */
    public function setHandler( Handler $handler );

}
