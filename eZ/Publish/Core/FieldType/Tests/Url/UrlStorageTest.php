<?php

/**
 * File containing the UrlStorageTest.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests\Url;

use eZ\Publish\Core\FieldType\Url\UrlStorage;
use eZ\Publish\SPI\FieldType\StorageGateway;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class UrlStorageTest extends TestCase
{
    public function testStoreFieldDataWithExistingUrl()
    {
        $versionInfo = new VersionInfo(array('versionNo' => 24));
        $fieldValue = new FieldValue(array('externalData' => 'http://ez.no'));
        $field = new Field(array('id' => 42, 'value' => $fieldValue));
        $gateway = $this->getGatewayMock();

        $gateway
            ->expects($this->once())
            ->method('getUrlIdMap')
            ->with(array('http://ez.no'))
            ->will($this->returnValue(array('http://ez.no' => 12)));

        $gateway
            ->expects($this->once())
            ->method('linkUrl')
            ->with(12, 42, 24);

        $storage = $this->getPartlyMockedStorage($gateway);
        $result = $storage->storeFieldData($versionInfo, $field, $this->getContext());

        $this->assertTrue($result);
        $this->assertEquals(12, $field->value->data['urlId']);
    }

    public function testStoreFieldDataWithNewUrl()
    {
        $versionInfo = new VersionInfo(array('versionNo' => 24));
        $fieldValue = new FieldValue(array('externalData' => 'http://ez.no'));
        $field = new Field(array('id' => 42, 'value' => $fieldValue));
        $gateway = $this->getGatewayMock();

        $gateway
            ->expects($this->once())
            ->method('getUrlIdMap')
            ->with(array('http://ez.no'))
            ->will($this->returnValue(array()));

        $gateway
            ->expects($this->once())
            ->method('insertUrl')
            ->with('http://ez.no')
            ->will($this->returnValue(12));

        $gateway
            ->expects($this->once())
            ->method('linkUrl')
            ->with(12, 42, 24);

        $storage = $this->getPartlyMockedStorage($gateway);
        $result = $storage->storeFieldData($versionInfo, $field, $this->getContext());

        $this->assertTrue($result);
        $this->assertEquals(12, $field->value->data['urlId']);
    }

    public function testStoreFieldDataWithEmptyUrl()
    {
        $versionInfo = new VersionInfo(array('versionNo' => 24));
        $fieldValue = new FieldValue(array('externalData' => ''));
        $field = new Field(array('id' => 42, 'value' => $fieldValue));
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

        $storage = $this->getPartlyMockedStorage($gateway);
        $result = $storage->storeFieldData($versionInfo, $field, $this->getContext());

        $this->assertFalse($result);
        $this->assertEquals(null, $field->value->data['urlId']);
    }

    public function testGetFieldData()
    {
        $versionInfo = new VersionInfo();
        $fieldValue = new FieldValue(array('data' => array('urlId' => 12)));
        $field = new Field(array('id' => 42, 'value' => $fieldValue));
        $gateway = $this->getGatewayMock();

        $gateway
            ->expects($this->once())
            ->method('getIdUrlMap')
            ->with(array(12))
            ->will($this->returnValue(array(12 => 'http://ez.no')));

        $storage = $this->getPartlyMockedStorage($gateway);
        $storage->getFieldData($versionInfo, $field, $this->getContext());

        $this->assertEquals('http://ez.no', $field->value->externalData);
    }

    public function testGetFieldDataNotFound()
    {
        $versionInfo = new VersionInfo();
        $fieldValue = new FieldValue(array('data' => array('urlId' => 12)));
        $field = new Field(array('id' => 42, 'value' => $fieldValue));
        $gateway = $this->getGatewayMock();

        $gateway
            ->expects($this->once())
            ->method('getIdUrlMap')
            ->with(array(12))
            ->will($this->returnValue(array()));

        $storage = $this->getPartlyMockedStorage($gateway);
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
        $fieldValue = new FieldValue(array('data' => array('urlId' => null)));
        $field = new Field(array('id' => 42, 'value' => $fieldValue));
        $gateway = $this->getGatewayMock();

        $gateway
            ->expects($this->never())
            ->method('getIdUrlMap');

        $logger = $this->getLoggerMock();
        $logger
            ->expects($this->never())
            ->method('error');

        $storage = $this->getPartlyMockedStorage($gateway);
        $storage->getFieldData($versionInfo, $field, $this->getContext());

        $this->assertEquals(null, $field->value->externalData);
    }

    public function testDeleteFieldData()
    {
        $versionInfo = new VersionInfo(array('versionNo' => 24));
        $fieldIds = array(12, 23, 34);
        $gateway = $this->getGatewayMock();

        foreach ($fieldIds as $index => $id) {
            $gateway
                ->expects($this->at($index))
                ->method('unlinkUrl')
                ->with($id, 24);
        }

        $storage = $this->getPartlyMockedStorage($gateway);
        $storage->deleteFieldData($versionInfo, $fieldIds, $this->getContext());
    }

    public function testHasFieldData()
    {
        $storage = $this->getPartlyMockedStorage($this->getGatewayMock());

        $this->assertTrue($storage->hasFieldData());
    }

    /**
     * @param \eZ\Publish\SPI\FieldType\StorageGateway $gateway
     * @return \eZ\Publish\Core\FieldType\Url\UrlStorage|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPartlyMockedStorage(StorageGateway $gateway)
    {
        return $this->getMockBuilder(UrlStorage::class)
            ->setMethods(null)
            ->setConstructorArgs(
                array(
                    $gateway,
                    $this->getLoggerMock(),
                )
            )
            ->getMock();
    }

    /**
     * @return array
     */
    protected function getContext()
    {
        return array('context');
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
                LoggerInterface::class
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
            $this->gatewayMock = $this->createMock(UrlStorage\Gateway::class);
        }

        return $this->gatewayMock;
    }
}
