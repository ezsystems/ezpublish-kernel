<?php
/**
 * File containing PHPUnit 5 Forward Compatibility trait.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Tests;

/**
 * Trait for PHPUnit 5 Forward Compatibility, for PHPUnit 4.8 use and up.
 *
 * @deprecated since 7.1, will be removed in 8.0. We are using PHPUnit 6, so this trait is obsolete.
 * Trait was used with PHPUnit v5 and v4, so basically trait can be removed when support period for 6.7 ends.
 */
trait PHPUnit5CompatTrait
{
    /**
     * @deprecated Since PHPUnit 5.4, marked as deprecated here to make it clear when working on 6.7/5.4 branches
     * {@inheritdoc}
     */
    public function getMock($originalClassName, $methods = [], array $arguments = [], $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $cloneArguments = false, $callOriginalMethods = false, $proxyTarget = null)
    {
        return parent::getMock(
            $originalClassName,
            $methods,
            $arguments,
            $mockClassName,
            $callOriginalConstructor,
            $callOriginalClone,
            $callAutoload,
            $cloneArguments,
            $callOriginalMethods,
            $proxyTarget
        );
    }
}
