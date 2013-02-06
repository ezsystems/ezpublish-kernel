<?php
/**
 * File containing the ImageProcessor class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\FieldTypeProcessor;

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
     * Perform manipulations on an a generated $outgoingValueHash
     *
     * This method is called by the REST server to allow a field type to post
     * process the given $outgoingValueHash, which was previously generated
     * using {@link eZ\Publish\SPI\FieldType\FieldType::toHash()}, before it is
     * sent to the client. The return value of this method replaces
     * $outgoingValueHash and must obey to the same rules as the original
     * $outgoingValueHash.
     *
     * @param mixed $outgoingValueHash
     *
     * @return mixed Post processed hash
     */
    public function postProcessHash( $outgoingValueHash )
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
        if ( preg_match( '((?:^|/)([0-9a-f]+)(?:\.[a-z0-9]+)?)', $path, $matches ) )
        {
            $path = $matches[1];
        }

        return str_replace(
            '{path}',
            $path,
            $this->urlTemplate
        );
    }
}
