<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\HttpCache\Controller;

use eZ\Publish\Core\REST\Server\HttpCache\Controller\CachedValueUnwrapperController;
use eZ\Publish\Core\REST\Server\Values\CachedValue;
use PHPUnit_Framework_TestCase;
use stdClass;

class CachedValueUnwrapperControllerTest extends PHPUnit_Framework_TestCase
{
    public function testAnyMethodWithCachedValue()
    {
        $method = $this->getRandomMethod();

        $innerControllerMock = $this->getMockBuilder('stdClass')->setMethods([$method])->getMock();
        $controller = new CachedValueUnwrapperController($innerControllerMock);

        $value = new stdClass();

        $innerControllerMock
            ->expects($this->once())
            ->method($method)
            ->will($this->returnValue(new CachedValue($value)));

        $this->assertSame($value, $controller->$method());
    }

    public function testAnyMethodWithoutCachedValue()
    {
        $method = $this->getRandomMethod();

        $innerControllerMock = $this->getMockBuilder('stdClass')->setMethods([$method])->getMock();
        $controller = new CachedValueUnwrapperController($innerControllerMock);

        $value = new stdClass();

        $innerControllerMock
            ->expects($this->once())
            ->method($method)
            ->will($this->returnValue($value));

        $this->assertSame($value, $controller->$method());
    }

    /**
     * @return string
     */
    protected function getRandomMethod(): string
    {
        $actions = ['create', 'retrieve', 'update', 'delete'];
        $subjects = ['content', 'section', 'location'];

        return $actions[array_rand($actions)] . ucfirst($subjects[array_rand($subjects)]);
    }
}
