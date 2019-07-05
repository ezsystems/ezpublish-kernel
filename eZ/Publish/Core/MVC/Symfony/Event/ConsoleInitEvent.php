<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Event;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Allows to do things before the command is loaded, like configuring system based on input.
 */
class ConsoleInitEvent extends ConsoleEvent
{
    public function __construct(InputInterface $input, OutputInterface $output)
    {
        // Only relevant for 6.x, as in Symfony 2.x command argument is not optional
        if (Kernel::MAJOR_VERSION < 3) {
            parent::__construct(new Command('invalid'), $input, $output);
        } else {
            parent::__construct(null, $input, $output);
        }
    }
}
