<?php

/**
 * File containing the ImageProcessor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\FieldTypeProcessor;

use Symfony\Component\Routing\RouterInterface;

class ImageProcessor extends BinaryInputProcessor
{
    /**
     * Template for image URLs.
     *
     * @var string
     */
    protected $urlTemplate;

    /**
     * Array of variations identifiers.
     *
     * <code>
     * array( 'small', 'thumbnail', 'large' )
     * </code>
     *
     * @var string[]
     */
    protected $variations;

    /** @var RouterInterface */
    protected $router;

    /**
     * @param string $temporaryDirectory
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param array $variations array of variations identifiers
     */
    public function __construct($temporaryDirectory, RouterInterface $router, array $variations)
    {
        parent::__construct($temporaryDirectory);
        $this->router = $router;
        $this->variations = $variations;
    }

    public function preProcessValueHash($incomingValueHash)
    {
        if (is_array($incomingValueHash) && array_key_exists('variations', $incomingValueHash)) {
            unset($incomingValueHash['variations']);
        }

        return parent::preProcessValueHash($incomingValueHash);
    }

    /**
     * {@inheritdoc}
     */
    public function postProcessValueHash($outgoingValueHash)
    {
        if (!is_array($outgoingValueHash)) {
            return $outgoingValueHash;
        }

        $outgoingValueHash['path'] = '/' . $outgoingValueHash['path'];
        foreach ($this->variations as $variationIdentifier) {
            $outgoingValueHash['variations'][$variationIdentifier] = [
                'href' => $this->router->generate(
                    'ezpublish_rest_binaryContent_getImageVariation',
                    [
                        'imageId' => $outgoingValueHash['imageId'],
                        'variationIdentifier' => $variationIdentifier,
                    ]
                ),
            ];
        }

        return $outgoingValueHash;
    }

    /**
     * Generates a URL for $path in $variation.
     *
     * @param string $path
     * @param string $variation
     *
     * @return string
     */
    protected function generateUrl($path, $variation)
    {
        $fieldId = '';
        $versionNo = '';

        // 223-1-eng-US/Cool-File.jpg
        if (preg_match('((?<id>[0-9]+)-(?<version>[0-9]+)-[^/]+/[^/]+$)', $path, $matches)) {
            $fieldId = $matches['id'];
            $versionNo = $matches['version'];
        }

        return str_replace(
            [
                '{variation}',
                '{fieldId}',
                '{versionNo}',
            ],
            [
                $variation,
                $fieldId,
                $versionNo,
            ],
            $this->urlTemplate
        );
    }
}
