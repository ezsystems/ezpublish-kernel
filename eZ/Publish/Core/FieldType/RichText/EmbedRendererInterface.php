<?php
/**
 * File containing the RichText EmbedRenderer interface
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\RichText;

/**
 * RichText field type embed renderer interface, to be implemented in MVC layer.
 */
interface EmbedRendererInterface
{
    /**
     * Renders Content embed view
     *
     * @param int|string $contentId
     * @param string $viewType
     * @param array $parameters
     *
     * @return string
     */
    public function renderContent( $contentId, $viewType, array $parameters );

    /**
     * Renders Location embed view
     *
     * @param int|string $locationId
     * @param string $viewType
     * @param array $parameters
     *
     * @return string
     */
    public function renderLocation( $locationId, $viewType, array $parameters );
}
