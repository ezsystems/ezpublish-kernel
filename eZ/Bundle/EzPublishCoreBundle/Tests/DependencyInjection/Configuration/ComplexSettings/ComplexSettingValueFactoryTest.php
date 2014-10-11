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

class ArgumentValueFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testGetArgumentValue()
    {
        $factory = new ComplexSettingValueFactory( '/mnt/nfs/$var_dir$/$storage_dir$' );
        $factory->setDynamicSetting( array( '$var_dir$' ), 'var/ezdemo_site' );
        $factory->setDynamicSetting( array( '$storage_dir$' ), 'storage' );

        self::assertEquals(
            '/mnt/nfs/var/ezdemo_site/storage',
            $factory->getArgumentValue()
        );
    }
}
