<?php
/**
 * File containing the CoreExtension class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension;

use Twig_Extension;
use eZ\Publish\Core\MVC\Symfony\Templating\GlobalHelper;

class CoreExtension extends Twig_Extension
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Templating\GlobalHelper
     */
    private $globalHelper;

    public function __construct( GlobalHelper $globalHelper )
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
        return array( 'ezpublish' => $this->globalHelper );
    }
}
