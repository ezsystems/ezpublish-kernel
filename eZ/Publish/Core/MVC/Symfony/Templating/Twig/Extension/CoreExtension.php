<?php

/**
 * File containing the CoreExtension class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension;

use Twig_Extension;
use eZ\Publish\Core\MVC\Symfony\Templating\GlobalHelper;
use Twig_Extension_GlobalsInterface;

class CoreExtension extends Twig_Extension implements Twig_Extension_GlobalsInterface
{
    /** @var \eZ\Publish\Core\MVC\Symfony\Templating\GlobalHelper */
    private $globalHelper;

    public function __construct(GlobalHelper $globalHelper)
    {
        $this->globalHelper = $globalHelper;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'ezpublish.core';
    }

    /**
     * @return array
     */
    public function getGlobals()
    {
        return ['ezpublish' => $this->globalHelper];
    }
}
