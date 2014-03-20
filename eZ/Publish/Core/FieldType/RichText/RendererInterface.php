<?php
/**
 * File containing the RichText Renderer interface
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\RichText;

/**
 * RichText field type renderer interface, to be implemented in MVC layer.
 */
interface RendererInterface
{
    /**
     * Renders template tag
     *
     * @param string $name
     * @param array $parameters
     *
     * @return string
     */
    public function renderTag( $name, array $parameters );

    /**
     * Renders Content embed
     *
     * @param int|string $contentId
     * @param string $viewType
     * @param array $parameters
     * @param boolean $isInline
     *
     * @return string
     */
    public function renderContentEmbed( $contentId, $viewType, array $parameters, $isInline );

    /**
     * Renders Location embed
     *
     * @param int|string $locationId
     * @param string $viewType
     * @param array $parameters
     * @param boolean $isInline
     *
     * @return string
     */
    public function renderLocationEmbed( $locationId, $viewType, array $parameters, $isInline );
}
