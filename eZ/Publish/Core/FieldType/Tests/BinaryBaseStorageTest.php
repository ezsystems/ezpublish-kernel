<?php
/**
 * File containing the BinaryFileStorage class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\BinaryBase\BinaryBaseStorage;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Description of BinaryFileStorage
 */
class BinaryBaseStorageTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {

    }

    public function testGetFileDataEmptyId()
    {
        $storage = $this->getBinaryFileStorage();

        $versionInfo = new VersionInfo(
            array( 'versionNo' => 1 )
        );

        $field = new Field(
            array(
                'id' => 2,
                'value' => new FieldValue()
            )
        );

        $this->getGatewayMock()
            ->expects( $this->once() )
            ->method( 'getFileReferenceData' )
            ->with( $field->id, $versionInfo->versionNo )
            ->will( $this->returnValue( array( 'id' => '' ) ) );

        $storage->getFieldData( $versionInfo, $field, array( 'identifier' => 'test', 'connection' => false ) );
    }

    /**
     * @return BinaryBaseStorage
     */
    protected function getBinaryFileStorage()
    {
        if ( !isset( $this->dependencies['binaryBaseStorage'] ) )
        {
            $this->dependencies['binaryBaseStorage'] = new BinaryBaseStorage(
                array( 'test' => $this->getGatewayMock() ),
                $this->getIOServiceMock(),
                $this->getPathGeneratorMock(),
                $this->getMimeTypeDetectorMock()
            );
        }
        return $this->dependencies['binaryBaseStorage'];
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|BinaryBaseStorage\Gateway
     */
    protected function getGatewayMock()
    {
        if ( !isset( $this->dependencies['gatewayMock'] ) )
        {
            $this->dependencies['gatewayMock'] = $this->getMockForAbstractClass(
                'eZ\Publish\Core\FieldType\BinaryBase\BinaryBaseStorage\Gateway'
            );
        }

        return $this->dependencies['gatewayMock'];
    }

    /**
     * @return \eZ\Publish\Core\IO\IOService|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getIOServiceMock()
    {
        if ( !isset( $this->dependencies['IOServiceMock'] ) )
        {
            $this->dependencies['IOServiceMock'] = $this->getMockForAbstractClass(
                'eZ\Publish\Core\IO\IOService',
                array(),
                '',
                false
            );
        }

        return $this->dependencies['IOServiceMock'];
    }

    /**
     * @return \eZ\Publish\SPI\FieldType\BinaryBase\PathGenerator |PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPathGeneratorMock()
    {
        if ( !isset( $this->dependencies['pathGeneratorMock'] ) )
        {
            $this->dependencies['pathGeneratorMock'] = $this->getMockForAbstractClass(
                'eZ\Publish\SPI\FieldType\BinaryBase\PathGenerator'
            );
        }

        return $this->dependencies['pathGeneratorMock'];
    }

    /**
     * @return \eZ\Publish\SPI\IO\MimeTypeDetector|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMimeTypeDetectorMock()
    {
        if ( !isset( $this->dependencies['mimeTypeDetectorMock'] ) )
        {
            $this->dependencies['mimeTypeDetectorMock'] = $this->getMockForAbstractClass(
                'eZ\Publish\SPI\IO\MimeTypeDetector'
            );
        }

        return $this->dependencies['mimeTypeDetectorMock'];
    }

    /**
     * @var object[]
     */
    protected $dependencies;
}
