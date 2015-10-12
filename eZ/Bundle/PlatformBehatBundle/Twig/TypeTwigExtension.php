<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\PlatformBehatBundle\Twig;

use Pagerfanta\Pagerfanta;

class TypeTwigExtension extends \Twig_Extension
{
    public function isBoolean($var)
    {
        return is_bool($var);
    }
    public function isAnArray($var)
    {
        return is_array($var);
    }

    public function isAPager($var)
    {
        return $var instanceof Pagerfanta;
    }

    public function getTests()
    {
        return array(
            'anArray' => new \Twig_Function_Method($this, 'isAnArray'),
            'aPager' => new \Twig_Function_Method($this, 'isAPager'),
        );
    }

    public function getName()
    {
        return 'EzSystemsPlatformBehatTypeOfTwigExtension';
    }
}
