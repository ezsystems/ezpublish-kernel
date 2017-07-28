<?php

namespace eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader;

use Imagine\Image\Fill\Gradient\Horizontal;
use Imagine\Image\ImageInterface;
use Imagine\Exception\InvalidArgumentException;

class OverlayGradientHorizontalLoader extends FilterLoaderWrapped
{
    public function load(ImageInterface $image, array $options = array())
    {
        if (count($options) < 3) {
            throw new InvalidArgumentException('Missing opacity and/or colors option(s)');
        }

        if (isset($options['opacity'])) {
            $options[0] = $options['opacity'];
        }
        if (isset($options['start_color'])) {
            $options[1] = $options['start_color'];
        }
        if (isset($options['end_color'])) {
            $options[2] = $options['end_color'];
        }

        return $this->innerLoader->load($image, [
            'opacity' => $options[0],
            'startColor' => $options[1],
            'endColor' => $options[2],
            'linerClass' => Horizontal::class,
        ]);
    }
}
