<?php

/**
 * File containing the Application class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Console;

use eZ\Publish\Core\MVC\Symfony\Event\ConsoleInitEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use Symfony\Bundle\FrameworkBundle\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * eZ Publish console application.
 * Adds options specific to an eZ Publish environment, such as the siteaccess to use.
 */
class Application extends BaseApplication
{
    /**
     * @see doRun
     *
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        parent::__construct($kernel);
        $this->getDefinition()->addOption(
            new InputOption('--siteaccess', null, InputOption::VALUE_OPTIONAL, 'SiteAccess to use for operations. If not provided, default siteaccess will be used')
        );
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        // boot() will be re-executed by parent, but kernel only boots once regardlessly
        $this->kernel->boot();

        // @todo Contribute a console.init event to Symfony4 in order to rather use that in v3 to drop doRun() overload
        $this->kernel->getContainer()->get('event_dispatcher')->dispatch(
            MVCEvents::CONSOLE_INIT,
            new ConsoleInitEvent($input, $output)
        );

        return parent::doRun($input, $output);
    }
}
