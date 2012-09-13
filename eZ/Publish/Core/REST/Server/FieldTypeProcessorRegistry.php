<?php
/**
 * File containing the FieldTypeProcessorRegistry class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server;

/**
 * FieldTypeProcessorRegistry
 */
class FieldTypeProcessorRegistry
{
    /**
     * Registered processors
     *
     * @var \eZ\Publish\Core\REST\Server\FieldTypeProcessor[]
     */
    private $processors = array();

    /**
     * @param \eZ\Publish\Core\REST\Server\FieldTypeProcessor[] $processors
     */
    public function __construct( array $processors = array() )
    {
        foreach ( $processors as $fieldTypeIdentifier => $processor )
        {
            $this->registerProcessor( $fieldTypeIdentifier, $processor );
        }
    }

    /**
     * Registers $processor for $fieldTypeIdentifier
     *
     * @param string $fieldTypeIdentifier
     * @param \eZ\Publish\Core\REST\Server\FieldTypeProcessor $processor
     * @return void
     */
    public function registerProcessor( $fieldTypeIdentifier, FieldTypeProcessor $processor )
    {
        throw new \RuntimeException( '@TODO: Implement.' );
    }

    /**
     * Returns if a processor is registered for $fieldTypeIdentifier
     *
     * @param string $fieldTypeIdentifier
     * @return bool
     */
    public function hasProcessor( $fieldTypeIdentifier )
    {
        throw new \RuntimeException( '@TODO: Implement.' );
    }

    /**
     * Returns the processor for $fieldTypeIdentifier
     *
     * @param mixed $fieldTypeIdentifier
     * @return \eZ\Publish\Core\REST\Server\FieldTypeProcessor
     * @throws NotFoundException if not processor is registered for
     *                           $fieldTypeIdentifier
     */
    public function getProcessor( $fieldTypeIdentifier )
    {
        throw new \RuntimeException( '@TODO: Implement.' );
    }
}
