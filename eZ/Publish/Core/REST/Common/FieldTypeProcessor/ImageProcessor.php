<?php
/**
 * File containing the ImageProcessor class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\FieldTypeProcessor;

use eZ\Publish\Core\REST\Common\UrlHandler;

class ImageProcessor extends BinaryInputProcessor
{
    /**
     * Template for image URLs
     *
     * @var string
     */
    protected $urlTemplate;

    /**
     * Array of variants identifiers
     *
     * <code>
     * array( 'small', 'thumbnail', 'large' )
     * </code>
     *
     * @var string[]
     */
    protected $variants;

    /**
     * @var \eZ\Publish\Core\REST\Common\UrlHandler
     */
    protected $urlHandler;

    /**
     * @param string $temporaryDirectory
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     * @param array $variants
     */
    public function __construct( $temporaryDirectory, $urlHandler, array $variants )
    {
        parent::__construct( $temporaryDirectory );
        $this->urlHandler = $urlHandler;
        $this->variants = $variants;
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

        $outgoingValueHash['path'] = '/' . $outgoingValueHash['path'];
        foreach( $this->variants as $variationIdentifier )
        {
            $outgoingValueHash['variants'][$variationIdentifier] = array(
                'href' => $this->urlHandler->generate(
                    'getImageVariation',
                    array(
                        'imageId' => $outgoingValueHash['imageId'],
                        'variationIdentifier' => $variationIdentifier
                    )
                ),
            );
        }

        return $outgoingValueHash;
    }

    /**
     * Generates a URL for $path in $variant
     *
     * @param string $path
     * @param string $variant
     *
     * @return string
     */
    protected function generateUrl( $path, $variant )
    {
        $fieldId = '';
        $versionNo = '';

        // 223-1-eng-US/Cool-File.jpg
        if ( preg_match( '((?<id>[0-9]+)-(?<version>[0-9]+)-[^/]+/[^/]+$)', $path, $matches ) )
        {
            $fieldId = $matches['id'];
            $versionNo = $matches['version'];
        }

        return str_replace(
            array(
                '{variant}',
                '{fieldId}',
                '{versionNo}',
            ),
            array(
                $variant,
                $fieldId,
                $versionNo
            ),
            $this->urlTemplate
        );
    }
}
