<?php
/**
 * File containing the RichTextStorageTest
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\DomainLogic\Tests\FieldType\RichText;

use eZ\Publish\Core\FieldType\RichText\RichTextStorage;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use PHPUnit_Framework_TestCase;

/**
 * @package eZ\Publish\Core\Repository\DomainLogic\Tests\FieldType\RichText\Gateway
 */
class RichTextStorageTest extends PHPUnit_Framework_TestCase
{
    public function providerForTestGetFieldData()
    {
        return array(
            array(
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
                array( 123, 456 ),
                array( 123 => "http://www.ez.no" )
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>Oh links, where art thou?</para>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>Oh links, where art thou?</para>
</section>
',
                array(),
                array()
            ),
        );
    }

    /**
     * @dataProvider providerForTestGetFieldData
     */
    public function testGetFieldData( $xmlString, $updatedXmlString, $linkIds, $linkUrls )
    {
        $gateway = $this->getGatewayMock();
        $gateway
            ->expects( $this->once() )
            ->method( "getLinkUrls" )
            ->with( $this->equalTo( $linkIds ) )
            ->will( $this->returnValue( $linkUrls ) );
        $gateway->expects( $this->never() )->method( "getLinkIds" );
        $gateway->expects( $this->never() )->method( "getContentIds" );
        $gateway->expects( $this->never() )->method( "insertLink" );

        $versionInfo = new VersionInfo();
        $value = new FieldValue( array( "data" => $xmlString ) );
        $field = new Field( array( "value" => $value ) );

        $storage = $this->getPartlyMockedStorage( array( "getGateway" ) );
        $storage
            ->expects( $this->once() )
            ->method( "getGateway" )
            ->with( $this->getContext() )
            ->will( $this->returnValue( $gateway ) );
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
        return array(
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>
        <link xlink:href="ezremote://abcdef789#fragment1">Content link</link>
    </para>
    <para>
        <link xlink:href="http://www.ez.no#fragment2">Existing external link</link>
    </para>
    <para>
        <link xlink:href="http://share.ez.no#fragment3">New external link</link>
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
        <link xlink:href="ezurl://456#fragment3">New external link</link>
    </para>
</section>
',
                array( "http://www.ez.no", "http://share.ez.no" ),
                array( "http://www.ez.no" => 123 ),
                array( array( "id" => 456, "url" => "http://share.ez.no" ) ),
                array( "abcdef789" ),
                array( "abcdef789" => 7575 ),
                true
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>Oh links, where art thou?</para>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>Oh links, where art thou?</para>
</section>
',
                array(),
                array(),
                array(),
                array(),
                array(),
                true
            ),
        );
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
    )
    {
        $gateway = $this->getGatewayMock();
        $gateway
            ->expects( $this->once() )
            ->method( "getLinkIds" )
            ->with( $this->equalTo( $linkUrls ) )
            ->will( $this->returnValue( $linkIds ) );
        $gateway
            ->expects( $this->once() )
            ->method( "getContentIds" )
            ->with( $this->equalTo( $remoteIds ) )
            ->will( $this->returnValue( $contentIds ) );
        $gateway->expects( $this->never() )->method( "getLinkUrls" );
        if ( empty( $insertLinks ) )
        {
            $gateway->expects( $this->never() )->method( "insertLink" );
        }

        foreach ( $insertLinks as $index => $linkMap )
        {
            $gateway
                ->expects( $this->at( $index + 2 ) )
                ->method( "insertLink" )
                ->with( $this->equalTo( $linkMap["url"] ) )
                ->will( $this->returnValue( $linkMap["id"] ) );
        }

        $versionInfo = new VersionInfo();
        $value = new FieldValue( array( "data" => $xmlString ) );
        $field = new Field( array( "value" => $value ) );

        $storage = $this->getPartlyMockedStorage( array( "getGateway" ) );
        $storage
            ->expects( $this->once() )
            ->method( "getGateway" )
            ->with( $this->getContext() )
            ->will( $this->returnValue( $gateway ) );
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
        return array(
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>
        <link xlink:href="ezremote://abcdef789#fragment1">Content link</link>
    </para>
</section>
',
                array(),
                array(),
                array(),
                array( "abcdef789" ),
                array()
            ),
        );
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
    )
    {
        $gateway = $this->getGatewayMock();
        $gateway
            ->expects( $this->once() )
            ->method( "getLinkIds" )
            ->with( $this->equalTo( $linkUrls ) )
            ->will( $this->returnValue( $linkIds ) );
        $gateway
            ->expects( $this->once() )
            ->method( "getContentIds" )
            ->with( $this->equalTo( $remoteIds ) )
            ->will( $this->returnValue( $contentIds ) );
        $gateway->expects( $this->never() )->method( "getLinkUrls" );
        if ( empty( $insertLinks ) )
        {
            $gateway->expects( $this->never() )->method( "insertLink" );
        }

        foreach ( $insertLinks as $index => $linkMap )
        {
            $gateway
                ->expects( $this->at( $index + 2 ) )
                ->method( "insertLink" )
                ->with( $this->equalTo( $linkMap["url"] ) )
                ->will( $this->returnValue( $linkMap["id"] ) );
        }

        $versionInfo = new VersionInfo();
        $value = new FieldValue( array( "data" => $xmlString ) );
        $field = new Field( array( "value" => $value ) );

        $storage = $this->getPartlyMockedStorage( array( "getGateway" ) );
        $storage
            ->expects( $this->once() )
            ->method( "getGateway" )
            ->with( $this->getContext() )
            ->will( $this->returnValue( $gateway ) );

        $storage->storeFieldData(
            $versionInfo,
            $field,
            $this->getContext()
        );
    }

    /**
     * @param array $methods
     *
     * @return \eZ\Publish\Core\FieldType\RichText\RichTextStorage|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPartlyMockedStorage( array $methods )
    {
        return $this->getMock(
            "eZ\\Publish\\Core\\FieldType\\RichText\\RichTextStorage",
            $methods,
            array(
                $this->getContext(),
                $this->getLoggerMock()
            ),
            "",
            false
        );
    }

    /**
     * @return array
     */
    protected function getContext()
    {
        return array( "context" );
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
        if ( !isset( $this->loggerMock ) )
        {
            $this->gatewayMock = $this->getMockForAbstractClass(
                "Psr\\Log\\LoggerInterface"
            );
        }

        return $this->loggerMock;
    }

    /**
     * @var \eZ\Publish\Core\FieldType\RichText\RichTextStorage\Gateway|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $gatewayMock;

    /**
     * @return \eZ\Publish\Core\FieldType\RichText\RichTextStorage\Gateway|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getGatewayMock()
    {
        if ( !isset( $this->gatewayMock ) )
        {
            $this->gatewayMock = $this->getMockForAbstractClass(
                "eZ\\Publish\\Core\\FieldType\\RichText\\RichTextStorage\\Gateway"
            );
        }

        return $this->gatewayMock;
    }
}
