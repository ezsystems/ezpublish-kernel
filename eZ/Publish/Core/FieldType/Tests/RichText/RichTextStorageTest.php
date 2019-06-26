<?php

/**
 * File containing the RichTextStorageTest.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests\RichText;

use eZ\Publish\Core\FieldType\RichText\RichTextStorage;
use eZ\Publish\SPI\FieldType\StorageGateway;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RichTextStorageTest extends TestCase
{
    public function providerForTestGetFieldData()
    {
        return [
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>
        <link xlink:href="ezurl://123#fragment1">Existing external link</link>
    </para>
    <para>
        <link xlink:href="ezurl://456#fragment2">Non-existing external link</link>
    </para>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>
        <link xlink:href="http://www.ez.no#fragment1">Existing external link</link>
    </para>
    <para>
        <link xlink:href="#">Non-existing external link</link>
    </para>
</section>
',
                [123, 456],
                [123 => 'http://www.ez.no'],
            ],
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>Oh links, where art thou?</para>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>Oh links, where art thou?</para>
</section>
',
                [],
                [],
            ],
        ];
    }

    /**
     * @dataProvider providerForTestGetFieldData
     */
    public function testGetFieldData($xmlString, $updatedXmlString, $linkIds, $linkUrls)
    {
        $gateway = $this->getGatewayMock();
        $gateway
            ->expects($this->once())
            ->method('getIdUrlMap')
            ->with($this->equalTo($linkIds))
            ->will($this->returnValue($linkUrls));
        $gateway->expects($this->never())->method('getUrlIdMap');
        $gateway->expects($this->never())->method('getContentIds');
        $gateway->expects($this->never())->method('insertUrl');

        $logger = $this->getLoggerMock();

        if (count($linkIds) !== count($linkUrls)) {
            $loggerInvocationCount = 0;

            foreach ($linkIds as $linkId) {
                if (!isset($linkUrls[$linkId])) {
                    $logger
                        ->expects($this->at($loggerInvocationCount))
                        ->method('error')
                        ->with("URL with ID {$linkId} not found");
                }
            }
        } else {
            $logger->expects($this->never())->method($this->anything());
        }

        $versionInfo = new VersionInfo();
        $value = new FieldValue(['data' => $xmlString]);
        $field = new Field(['value' => $value]);

        $storage = $this->getPartlyMockedStorage($gateway);
        $storage->getFieldData(
            $versionInfo,
            $field,
            $this->getContext()
        );

        $this->assertEquals(
            $updatedXmlString,
            $field->value->data
        );
    }

    public function providerForTestStoreFieldData()
    {
        return [
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>
        <link xlink:href="ezremote://abcdef789#fragment1">Content link</link>
    </para>
    <para>
        <link xlink:href="http://www.ez.no#fragment2">Existing external link</link>
    </para>
    <para>
        <link xlink:href="http://www.ez.no#fragment2">Existing external link repeated</link>
    </para>
    <para>
        <link xlink:href="http://share.ez.no#fragment3">New external link</link>
    </para>
    <para>
        <link xlink:href="http://share.ez.no#fragment3">New external link repeated</link>
    </para>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>
        <link xlink:href="ezcontent://7575#fragment1">Content link</link>
    </para>
    <para>
        <link xlink:href="ezurl://123#fragment2">Existing external link</link>
    </para>
    <para>
        <link xlink:href="ezurl://123#fragment2">Existing external link repeated</link>
    </para>
    <para>
        <link xlink:href="ezurl://456#fragment3">New external link</link>
    </para>
    <para>
        <link xlink:href="ezurl://456#fragment3">New external link repeated</link>
    </para>
</section>
',
                ['http://www.ez.no', 'http://share.ez.no'],
                ['http://www.ez.no' => 123],
                ['http://share.ez.no' => 456],
                ['abcdef789'],
                ['abcdef789' => 7575],
                true,
            ],
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>Oh links, where art thou?</para>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>Oh links, where art thou?</para>
</section>
',
                [],
                [],
                [],
                [],
                [],
                true,
            ],
        ];
    }

    /**
     * @dataProvider providerForTestStoreFieldData
     */
    public function testStoreFieldData(
        $xmlString,
        $updatedXmlString,
        $linkUrls,
        $linkIds,
        $insertLinks,
        $remoteIds,
        $contentIds,
        $isUpdated
    ) {
        $gatewayCallIndex = 0;
        $versionInfo = new VersionInfo(['versionNo' => 24]);
        $value = new FieldValue(['data' => $xmlString]);
        $field = new Field(['id' => 42, 'value' => $value]);
        $gateway = $this->getGatewayMock();

        $gateway
            ->expects($this->at($gatewayCallIndex++))
            ->method('getUrlIdMap')
            ->with($this->equalTo($linkUrls))
            ->will($this->returnValue($linkIds));
        $gateway
            ->expects($this->at($gatewayCallIndex++))
            ->method('getContentIds')
            ->with($this->equalTo($remoteIds))
            ->will($this->returnValue($contentIds));
        $gateway->expects($this->never())->method('getIdUrlMap');
        if (empty($insertLinks)) {
            $gateway->expects($this->never())->method('insertUrl');
        }

        foreach ($linkUrls as $url) {
            if (isset($insertLinks[$url])) {
                $id = $insertLinks[$url];
                $gateway
                    ->expects($this->at($gatewayCallIndex++))
                    ->method('insertUrl')
                    ->with($this->equalTo($url))
                    ->will($this->returnValue($id));
            } else {
                $id = $linkIds[$url];
            }

            $gateway
                ->expects($this->at($gatewayCallIndex++))
                ->method('linkUrl')
                ->with($id, 42, 24);
        }

        $storage = $this->getPartlyMockedStorage($gateway);
        $result = $storage->storeFieldData(
            $versionInfo,
            $field,
            $this->getContext()
        );

        $this->assertEquals(
            $isUpdated,
            $result
        );
        $this->assertEquals(
            $updatedXmlString,
            $field->value->data
        );
    }

    public function providerForTestStoreFieldDataThrowsNotFoundException()
    {
        return [
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>
        <link xlink:href="ezremote://abcdef789#fragment1">Content link</link>
    </para>
</section>
',
                [],
                [],
                [],
                ['abcdef789'],
                [],
            ],
        ];
    }

    /**
     * @dataProvider providerForTestStoreFieldDataThrowsNotFoundException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testStoreFieldDataThrowsNotFoundException(
        $xmlString,
        $linkUrls,
        $linkIds,
        $insertLinks,
        $remoteIds,
        $contentIds
    ) {
        $gateway = $this->getGatewayMock();
        $gateway
            ->expects($this->once())
            ->method('getUrlIdMap')
            ->with($this->equalTo($linkUrls))
            ->will($this->returnValue($linkIds));
        $gateway
            ->expects($this->once())
            ->method('getContentIds')
            ->with($this->equalTo($remoteIds))
            ->will($this->returnValue($contentIds));
        $gateway->expects($this->never())->method('getIdUrlMap');
        if (empty($insertLinks)) {
            $gateway->expects($this->never())->method('insertUrl');
        }

        foreach ($insertLinks as $index => $linkMap) {
            $gateway
                ->expects($this->at($index + 2))
                ->method('insertUrl')
                ->with($this->equalTo($linkMap['url']))
                ->will($this->returnValue($linkMap['id']));
        }

        $versionInfo = new VersionInfo();
        $value = new FieldValue(['data' => $xmlString]);
        $field = new Field(['value' => $value]);

        $storage = $this->getPartlyMockedStorage($gateway);
        $storage->storeFieldData(
            $versionInfo,
            $field,
            $this->getContext()
        );
    }

    public function testDeleteFieldData()
    {
        $versionInfo = new VersionInfo(['versionNo' => 42]);
        $fieldIds = [12, 23];
        $gateway = $this->getGatewayMock();
        $storage = $this->getPartlyMockedStorage($gateway);
        $gateway
            ->expects($this->at(0))
            ->method('unlinkUrl')
            ->with(12, 42);

        $gateway
            ->expects($this->at(1))
            ->method('unlinkUrl')
            ->with(23, 42);

        $storage->deleteFieldData(
            $versionInfo,
            $fieldIds,
            $this->getContext()
        );
    }

    /**
     * @param \eZ\Publish\SPI\FieldType\StorageGateway $gateway
     * @return \eZ\Publish\Core\FieldType\RichText\RichTextStorage|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getPartlyMockedStorage(StorageGateway $gateway)
    {
        return $this->getMockBuilder(RichTextStorage::class)
            ->setConstructorArgs(
                [
                    $gateway,
                    $this->getLoggerMock(),
                ]
            )
            ->setMethods(null)
            ->getMock();
    }

    /**
     * @return array
     */
    protected function getContext()
    {
        return ['context'];
    }

    /** @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $loggerMock;

    /**
     * @return \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
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

    /** @var \eZ\Publish\Core\FieldType\RichText\RichTextStorage\Gateway|\PHPUnit\Framework\MockObject\MockObject */
    protected $gatewayMock;

    /**
     * @return \eZ\Publish\Core\FieldType\RichText\RichTextStorage\Gateway|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getGatewayMock()
    {
        if (!isset($this->gatewayMock)) {
            $this->gatewayMock = $this->createMock(RichTextStorage\Gateway::class);
        }

        return $this->gatewayMock;
    }
}
