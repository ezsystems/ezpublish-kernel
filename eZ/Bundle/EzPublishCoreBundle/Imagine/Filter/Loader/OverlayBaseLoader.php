<?php

namespace eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader;

use Imagine\Filter\Basic\Fill;
use Imagine\Image\Fill\Gradient\Horizontal;
use Imagine\Image\Fill\Gradient\Vertical;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;
use Imagine\Image\Box;
use Imagine\Exception\InvalidArgumentException;

class OverlayBaseLoader extends FilterLoaderWrapped
{
    public function load(ImageInterface $image, array $options = array())
    {
        if (!isset($options['opacity'], $options['startColor'], $options['endColor'], $options['linerClass'])) {
            throw new InvalidArgumentException('Missing one of required options');
        }
        
        $horizontalLinerClass = Horizontal::class;
        $verticalLinerClass = Vertical::class;
        if (!\in_array($options['linerClass'], [$horizontalLinerClass, $verticalLinerClass], true)){
            throw new InvalidArgumentException(
                'Unsuported the "linerClass" it should be "' . $horizontalLinerClass . '" or "'
                . $verticalLinerClass . '"'
            );
        }
        $startOpacity = $endOpacity = $options['opacity'];
        if (is_array($options['opacity'])) {
            if (count($options['opacity']) < 2) {
                throw new InvalidArgumentException('Opacity should contains 2 parameters or should be a string, array given');
            }
            list($startOpacity, $endOpacity) = $options['opacity'];
        }
        
        // build start/end colors from options
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
        unset($palette); // don't needed more

        $imageSize = $image->getSize();
        $startPoint = new Point(0, 0);
        $overlay = $image->copy();
        if ($options['linerClass'] === $horizontalLinerClass) {
            $imageWidth = $imageSize->getWidth();
            $liner = new Horizontal($imageWidth, $startColor, $endColor);
            $overlay->crop($startPoint, new Box($imageWidth, 1));
        } else {
            $imageHeight = $imageSize->getHeight();
            $liner = new Vertical($imageHeight, $startColor, $endColor);
            $overlay->crop($startPoint, new Box(1, $imageHeight));
        }

        $filler = new Fill($liner);
        $overlay = $filler->apply($overlay);
        $overlay->resize($imageSize);

        return $image->paste($overlay, $startPoint);        
    }
}
