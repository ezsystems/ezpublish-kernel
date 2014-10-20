<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\ComplexSettings;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ComplexSettings\ComplexSettingValueFactory;
use PHPUnit_Framework_TestCase;

class ComplexSettingValueFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testGetArgumentValue()
    {
        self::assertEquals(
            '/mnt/nfs/var/ezdemo_site/storage',
            ComplexSettingValueFactory::getArgumentValue(
                '/mnt/nfs/$var_dir$/$storage_dir$',
                'var_dir',
                'var/ezdemo_site',
                'storage_dir',
                'storage'
            )
        );
    }
}
