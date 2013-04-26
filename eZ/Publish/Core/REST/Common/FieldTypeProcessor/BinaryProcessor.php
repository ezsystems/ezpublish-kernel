<?php
/**
 * File containing the ImageProcessor class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\FieldTypeProcessor;

class BinaryProcessor extends BinaryInputProcessor
{
    /**
     * Template for binary URLs
     *
     * The template may contain a "{path}" variable, which is replaced by the
     * MD5 file name part of the binary path.
     *
     * @var string
     */
    protected $urlTemplate;

    /**
     * @param string $temporaryDirectory
     * @param string $urlTemplate
     */
    public function __construct( $temporaryDirectory, $urlTemplate )
    {
        parent::__construct( $temporaryDirectory );
        $this->urlTemplate = $urlTemplate;
    }

    /**
     * {@inheritDoc}
     */
    public function postProcessValueHash( $outgoingValueHash )
    {
        if ( !is_array( $outgoingValueHash ) )
        {
            return $outgoingValueHash;
        }

        $outgoingValueHash['url'] = $this->generateUrl(
            $outgoingValueHash['path']
        );
        return $outgoingValueHash;
    }

    /**
     * Generates a URL for $path
     *
     * @param string $path
     *
     * @return string
     */
    protected function generateUrl( $path )
    {
        return str_replace(
            '{path}',
            $path,
            $this->urlTemplate
        );
    }
}
