<?php

namespace eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader;

use Imagine\Filter\Basic\Fill;
use Imagine\Image\Fill\Gradient\Horizontal;
use Imagine\Image\Fill\Gradient\Vertical;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;
use Imagine\Exception\InvalidArgumentException;

class OverlayBaseLoader extends FilterLoaderWrapped
{
    public function load(ImageInterface $image, array $options = array())
    {
        if (!isset($options['opacity'], $options['startColor'], $options['endColor'], $options['linerClass'])) {
            throw new InvalidArgumentException('Missing one of required options');
        }

        $startOpacity = $endOpacity = $options['opacity'];
        if (is_array($options['opacity'])) {
            if (count($options['opacity']) < 2) {
                throw new InvalidArgumentException('Opacity should contains 2 parameters or should be a string, array given');
            }
            list($startOpacity, $endOpacity) = $options['opacity'];
        }

        $imageSize = $image->getSize();
        $palette = $image->palette();

        $startColor = $palette->color($options['startColor'], $options['opacity']);
        switch (true) {
            case strpos($options['endColor'], '+') === 0:
                $endColor = $palette->color($options['endColor'], $endOpacity)
                    ->lighten(str_replace('+', '', $options['endColor']));
                break;
            case strpos($options['endColor'], '-') === 0:
                $endColor = $palette->color($options['endColor'], $endOpacity)
                    ->darken(str_replace('-', '', $options['endColor']));
                break;
            default:
                $endColor = $palette->color($options['endColor'], $endOpacity);
                break;
        }

        if ($options['linerClass'] === Horizontal::class) {
            $linerSize = $imageSize->getWidth();
        } else {
            $linerSize = $imageSize->getHeight();
        }

        $overlay = $image->copy();
        /** @var Horizontal|Vertical $liner */
        $liner = new $options['linerClass']($linerSize, $startColor, $endColor);
        $filter = new Fill($liner);
        $overlay = $filter->apply($overlay);

        return $image->paste($overlay, new Point(0, 0));
    }
}
