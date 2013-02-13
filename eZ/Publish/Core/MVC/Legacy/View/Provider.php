<?php
/**
 * File containing the Provider abstract class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\View;

use eZ\Publish\Core\MVC\Legacy\View\TwigContentViewLayoutDecorator;
use eZ\Publish\Core\MVC\Legacy\Templating\LegacyHelper;
use Psr\Log\LoggerInterface;
use Closure;

abstract class Provider
{
    /**
     * @var \Closure
     */
    private $legacyKernelClosure;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \eZ\Publish\Core\MVC\Legacy\View\TwigContentViewLayoutDecorator
     */
    protected $decorator;

    /**
     * @var \eZ\Publish\Core\MVC\Legacy\Templating\LegacyHelper
     */
    protected $legacyHelper;

    public function __construct( Closure $legacyKernelClosure, TwigContentViewLayoutDecorator $decorator, LegacyHelper $legacyHelper, LoggerInterface $logger = null )
    {
        $this->legacyKernelClosure = $legacyKernelClosure;
        $this->decorator = $decorator;
        $this->legacyHelper = $legacyHelper;
        $this->logger = $logger;
    }

    /**
     * @return \eZ\Publish\Core\MVC\Legacy\Kernel
     */
    protected function getLegacyKernel()
    {
        $legacyKernelClosure = $this->legacyKernelClosure;
        return $legacyKernelClosure();
    }
}
