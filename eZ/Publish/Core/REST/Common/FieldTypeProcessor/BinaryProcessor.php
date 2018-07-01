<?php

/**
 * File containing the ImageProcessor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\FieldTypeProcessor;

class BinaryProcessor extends BinaryInputProcessor
{
    /**
     * Host prefix for uris, without a leading /.
     *
     * @todo Refactor such transformation with a service that receives the request and has the host
     *
     * @var string
     */
    protected $hostPrefix;

    /**
     * @param string $temporaryDirectory
     * @param string $hostPrefix
     */
    public function __construct($temporaryDirectory, $hostPrefix)
    {
        parent::__construct($temporaryDirectory);
        $this->hostPrefix = $hostPrefix;
    }

    /**
     * {@inheritdoc}
     */
    public function postProcessValueHash($outgoingValueHash)
    {
        if (!is_array($outgoingValueHash)) {
            return $outgoingValueHash;
        }

        $outgoingValueHash['uri'] = $this->generateUrl($outgoingValueHash['uri']);

        // url is kept for BC, but uri is the right one
        $outgoingValueHash['url'] = $outgoingValueHash['uri'];

        return $outgoingValueHash;
    }

    /**
     * Generates a URL for $path.
     *
     * @param string $path absolute url
     *
     * @return string
     */
    protected function generateUrl($path)
    {
        $url = $path;
        if ($this->hostPrefix) {
            // url should start with a /
            $url = $this->hostPrefix . $url;
        }

        return $url;
    }
}
