<?php

/**
 * File containing the UrlStorageTest.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests\Url;

use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use PHPUnit\Framework\TestCase;

class UrlStorageTest extends TestCase
{
    public function testStoreFieldDataWithExistingUrl()
    {
        $versionInfo = new VersionInfo(['versionNo' => 24]);
        $fieldValue = new FieldValue(['externalData' => 'http://ez.no']);
        $field = new Field(['id' => 42, 'value' => $fieldValue]);
        $gateway = $this->getGatewayMock();

        $gateway
            ->expects($this->once())
            ->method('getUrlIdMap')
            ->with(['http://ez.no'])
            ->will($this->returnValue(['http://ez.no' => 12]));

        $gateway
            ->expects($this->once())
            ->method('linkUrl')
            ->with(12, 42, 24);

        $storage = $this->getPartlyMockedStorage(['getGateway']);
        $storage
            ->expects($this->once())
            ->method('getGateway')
            ->with($this->getContext())
            ->will($this->returnValue($gateway));

        $result = $storage->storeFieldData($versionInfo, $field, $this->getContext());

        $this->assertTrue($result);
        $this->assertEquals(12, $field->value->data['urlId']);
    }

    public function testStoreFieldDataWithNewUrl()
    {
        $versionInfo = new VersionInfo(['versionNo' => 24]);
        $fieldValue = new FieldValue(['externalData' => 'http://ez.no']);
        $field = new Field(['id' => 42, 'value' => $fieldValue]);
        $gateway = $this->getGatewayMock();

        $gateway
            ->expects($this->once())
            ->method('getUrlIdMap')
            ->with(['http://ez.no'])
            ->will($this->returnValue([]));

        $gateway
            ->expects($this->once())
            ->method('insertUrl')
            ->with('http://ez.no')
            ->will($this->returnValue(12));

        $gateway
            ->expects($this->once())
            ->method('linkUrl')
            ->with(12, 42, 24);

        $storage = $this->getPartlyMockedStorage(['getGateway']);
        $storage
            ->expects($this->once())
            ->method('getGateway')
            ->with($this->getContext())
            ->will($this->returnValue($gateway));

        $result = $storage->storeFieldData($versionInfo, $field, $this->getContext());

        $this->assertTrue($result);
        $this->assertEquals(12, $field->value->data['urlId']);
    }

    public function testStoreFieldDataWithEmptyUrl()
    {
        $versionInfo = new VersionInfo(['versionNo' => 24]);
        $fieldValue = new FieldValue(['externalData' => '']);
        $field = new Field(['id' => 42, 'value' => $fieldValue]);
        $gateway = $this->getGatewayMock();

        $gateway
            ->expects($this->never())
            ->method('getUrlIdMap');

        $gateway
            ->expects($this->never())
            ->method('insertUrl');

        $gateway
            ->expects($this->never())
            ->method('linkUrl');

        $storage = $this->getPartlyMockedStorage(['getGateway']);
        $storage
            ->expects($this->once())
            ->method('getGateway')
            ->with($this->getContext())
            ->will($this->returnValue($gateway));

        $result = $storage->storeFieldData($versionInfo, $field, $this->getContext());

        $this->assertFalse($result);
        $this->assertNull($field->value->data['urlId']);
    }

    public function testGetFieldData()
    {
        $versionInfo = new VersionInfo();
        $fieldValue = new FieldValue(['data' => ['urlId' => 12]]);
        $field = new Field(['id' => 42, 'value' => $fieldValue]);
        $gateway = $this->getGatewayMock();

        $gateway
            ->expects($this->once())
            ->method('getIdUrlMap')
            ->with([12])
            ->will($this->returnValue([12 => 'http://ez.no']));

        $storage = $this->getPartlyMockedStorage(['getGateway']);
        $storage
            ->expects($this->once())
            ->method('getGateway')
            ->with($this->getContext())
            ->will($this->returnValue($gateway));

        $storage->getFieldData($versionInfo, $field, $this->getContext());

        $this->assertEquals('http://ez.no', $field->value->externalData);
    }

    public function testGetFieldDataNotFound()
    {
        $versionInfo = new VersionInfo();
        $fieldValue = new FieldValue(['data' => ['urlId' => 12]]);
        $field = new Field(['id' => 42, 'value' => $fieldValue]);
        $gateway = $this->getGatewayMock();

        $gateway
            ->expects($this->once())
            ->method('getIdUrlMap')
            ->with([12])
            ->will($this->returnValue([]));

        $storage = $this->getPartlyMockedStorage(['getGateway']);
        $storage
            ->expects($this->once())
            ->method('getGateway')
            ->with($this->getContext())
            ->will($this->returnValue($gateway));

        $logger = $this->getLoggerMock();
        $logger
            ->expects($this->once())
            ->method('error')
            ->with("URL with ID '12' not found");

        $storage->getFieldData($versionInfo, $field, $this->getContext());

        $this->assertEquals('', $field->value->externalData);
    }

    public function testGetFieldDataWithEmptyUrlId()
    {
        $versionInfo = new VersionInfo();
        $fieldValue = new FieldValue(['data' => ['urlId' => null]]);
        $field = new Field(['id' => 42, 'value' => $fieldValue]);
        $gateway = $this->getGatewayMock();

        $gateway
            ->expects($this->never())
            ->method('getIdUrlMap');

        $logger = $this->getLoggerMock();
        $logger
            ->expects($this->never())
            ->method('error');

        $storage = $this->getPartlyMockedStorage(['getGateway']);
        $storage
            ->expects($this->any())
            ->method('getGateway')
            ->with($this->getContext())
            ->will($this->returnValue($gateway));

        $storage->getFieldData($versionInfo, $field, $this->getContext());

        $this->assertNull($field->value->externalData);
    }

    public function testDeleteFieldData()
    {
        $versionInfo = new VersionInfo(['versionNo' => 24]);
        $fieldIds = [12, 23, 34];
        $gateway = $this->getGatewayMock();

        foreach ($fieldIds as $index => $id) {
            $gateway
                ->expects($this->at($index))
                ->method('unlinkUrl')
                ->with($id, 24);
        }

        $storage = $this->getPartlyMockedStorage(['getGateway']);
        $storage
            ->expects($this->once())
            ->method('getGateway')
            ->with($this->getContext())
            ->will($this->returnValue($gateway));

        $storage->deleteFieldData($versionInfo, $fieldIds, $this->getContext());
    }

    public function testHasFieldData()
    {
        $storage = $this->getPartlyMockedStorage(['getGateway']);

        $this->assertTrue($storage->hasFieldData());
    }

    /**
     * @param array $methods
     *
     * @return \eZ\Publish\Core\FieldType\Url\UrlStorage|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPartlyMockedStorage(array $methods = [])
    {
        return $this->getMock(
            'eZ\\Publish\\Core\\FieldType\\Url\\UrlStorage',
            $methods,
            [
                [],
                $this->getLoggerMock(),
            ]
        );
    }

    /**
     * @return array
     */
    protected function getContext()
    {
        return ['context'];
    }

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @return \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLoggerMock()
    {
        if (!isset($this->loggerMock)) {
            $this->loggerMock = $this->getMockForAbstractClass(
                'Psr\\Log\\LoggerInterface'
            );
        }

        return $this->loggerMock;
    }

    /**
     * @var \eZ\Publish\Core\FieldType\Url\UrlStorage\Gateway|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $gatewayMock;

    /**
     * @return \eZ\Publish\Core\FieldType\Url\UrlStorage\Gateway|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getGatewayMock()
    {
        if (!isset($this->gatewayMock)) {
            $this->gatewayMock = $this->getMockForAbstractClass(
                'eZ\\Publish\\Core\\FieldType\\Url\\UrlStorage\\Gateway'
            );
        }

        return $this->gatewayMock;
    }
}
