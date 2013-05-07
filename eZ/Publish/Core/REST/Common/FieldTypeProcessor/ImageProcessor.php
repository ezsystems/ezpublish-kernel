<?php
/**
 * File containing the ImageProcessor class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\FieldTypeProcessor;

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
