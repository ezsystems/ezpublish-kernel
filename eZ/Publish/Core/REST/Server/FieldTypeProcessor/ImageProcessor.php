<?php
/**
 * File containing the ImageProcessor class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\FieldTypeProcessor;

class ImageProcessor extends BinaryInputProcessor
{
    /**
     * Template for image URLs
     *
     * @var string
     */
    protected $urlTemplate;

    /**
     * Array of variant names and content types
     *
     * <code>
     * array(
     *      'small' => 'image/jpeg',
     *      'thumbnail' => 'image/png',
     * )
     * </code>
     *
     * @var string[][]
     */
    protected $variants;

    /**
     * @param string $temporaryDirectory
     * @param string $urlTemplate
     * @param array $variants
     */
    public function __construct( $temporaryDirectory, $urlTemplate, array $variants )
    {
        parent::__construct( $temporaryDirectory );
        $this->urlTemplate = $urlTemplate;
        $this->variants = $variants;
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
     * @return mixed Post processed hash
     */
    public function postProcessHash( $outgoingValueHash )
    {
        if ( !is_array( $outgoingValueHash ) )
        {
            return $outgoingValueHash;
        }

        $outgoingValueHash['variants'] = array();
        foreach ( $this->variants as $variant => $mimeType )
        {
            $outgoingValueHash['variants'][] = array(
                'variant' => $variant,
                'contentType' => $mimeType,
                'url' => $this->generateUrl(
                    $outgoingValueHash['path'],
                    $variant
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
