<?php

/**
 * File containing the Application class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Console;

use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Symfony\Bundle\FrameworkBundle\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * eZ Publish console application.
 * Adds options specific to an eZ Publish environment, such as the siteaccess to use.
 */
class Application extends BaseApplication
{
    public function __construct(KernelInterface $kernel)
    {
        parent::__construct($kernel);
        $this->getDefinition()->addOption(
            new InputOption('--siteaccess', null, InputOption::VALUE_OPTIONAL, 'SiteAccess to use for operations. If not provided, default siteaccess will be used')
        );
    }
}
