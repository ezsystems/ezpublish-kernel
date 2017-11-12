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
 */
trait PHPUnit5CompatTrait
{
    /**
     * @deprecated Since PHPUnit 5.4, marked as deprecated here to make it clear when working on 6.7/5.4 branches
     * {@inheritdoc}
     */
    public function getMock($originalClassName, $methods = array(), array $arguments = array(), $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $cloneArguments = false, $callOriginalMethods = false, $proxyTarget = null)
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

    /**
     * Returns a test double for the specified class.
     *
     * @internal Forward compatibility with PHPUnit 5/6, so unit tests written on 6.7 & backported to 5.4 can use this.
     *
     * @param string $originalClassName
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createMock($originalClassName)
    {
        return $this->getMockBuilder($originalClassName)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            //->disallowMockingUnknownTypes() Not defined in PHPunit 4.8
            ->getMock();
    }
}
