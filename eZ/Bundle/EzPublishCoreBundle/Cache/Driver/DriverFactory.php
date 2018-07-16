<?php

/**
 * File containing the DriverFactory class.
 *
 * @copyright Copyright (c) 2014, Robert Hafner, Josh Hall-Bachner. All rights reserved.
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 *
 * Original source: https://github.com/tedious/TedivmStashBundle/blob/master/Factory/DriverFactory.php
 *
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Cache\Driver;

use Stash\DriverList;
use Tedivm\StashBundle\Factory\DriverFactory as StashDriverFactory;

class DriverFactory extends StashDriverFactory
{
    public static function registerAndCreateDriver($name, $class, $types, $options)
    {
        DriverList::registerDriver($name, $class);

        return parent::createDriver($types, $options);
    }
}
