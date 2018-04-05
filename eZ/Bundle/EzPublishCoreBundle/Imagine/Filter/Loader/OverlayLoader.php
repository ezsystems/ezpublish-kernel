<?php

namespace eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader;

use Imagine\Image\Fill\Gradient\Horizontal;
use Imagine\Image\ImageInterface;
use Imagine\Exception\InvalidArgumentException;

class OverlayLoader extends FilterLoaderWrapped
{
    public function load(ImageInterface $image, array $options = array())
    {
        if (\count($options) < 2) {
            throw new InvalidArgumentException('Missing opacity and/or color option(s)');
        }
        if (isset($options['opacity'], $options['color'])) {
            $options[0] = $options['opacity'];
            $options[1] = $options['color'];
        }

        if (!isset($options[0], $options[1])) {
            throw new InvalidArgumentException('Unsupported configuration');
        }

        return $this->innerLoader->load($image, [
            'opacity' => $options[0],
            'startColor' => $options[1],
            'endColor' => $options[1],
            'linerClass' => Horizontal::class,
        ]);
    }
}
