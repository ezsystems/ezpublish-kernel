<?php
/**
 * File containing the DFSTest class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishDFSBundle\Tests;

use eZ\Bundle\EzPublishDFSBundle\eZ\IO\Handler\DFS;

class DFSTest extends \PHPUnit_Framework_TestCase
{
    /** @var \eZ\Bundle\EzPublishDFSBundle\eZ\IO\Handler\DBInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $DBMock;

    /** @var \eZ\Bundle\EzPublishDFSBundle\eZ\IO\Handler\FSInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $FSMock;

    /** @var DFS */
    protected $DFSIOHandler;

    public function testCreate()
    {
        self::markTestIncomplete( "Not implemented yet" );
    }

    public function testCreateAlreadyExists()
    {
        self::markTestIncomplete( "Not implemented yet" );
    }

    public function testDelete()
    {
        self::markTestIncomplete( "Not implemented yet" );
    }

    public function testDeleteNotFound()
    {
        self::markTestIncomplete( "Not implemented yet" );
    }

    public function testUpdate()
    {
        self::markTestIncomplete( "Not implemented yet" );
    }

    public function testUpdateSourceFound()
    {
        self::markTestIncomplete( "Not implemented yet" );
    }

    public function testUpdateTargetExists()
    {
        self::markTestIncomplete( "Not implemented yet" );
    }

    public function testExists()
    {
        self::markTestIncomplete( "Not implemented yet" );
    }

    public function testLoad()
    {
        self::markTestIncomplete( "Not implemented yet" );
    }

    public function testLoadNotFound()
    {
        self::markTestIncomplete( "Not implemented yet" );
    }

    public function testGetFileResource()
    {
        self::markTestIncomplete( "Not implemented yet" );
    }

    public function testGetFileResourceNotFound()
    {
        self::markTestIncomplete( "Not implemented yet" );
    }

    public function testGetFileContents()
    {
        self::markTestIncomplete( "Not implemented yet" );
    }

    public function testGetFileContentsNotFound()
    {
        self::markTestIncomplete( "Not implemented yet" );
    }

    public function testGetInternalPath()
    {
        self::markTestIncomplete( "Not implemented yet" );
    }

    public function testGetExternalPath()
    {
        self::markTestIncomplete( "Not implemented yet" );
    }

    public function testGetMetadata()
    {
        self::markTestIncomplete( "Not implemented yet" );
    }

    public function testGetMetadataNotFound()
    {
        self::markTestIncomplete( "Not implemented yet" );
    }

    public function testGetUri()
    {
        self::markTestIncomplete( "Not implemented yet" );
    }

    /**
     * @return \eZ\Bundle\EzPublishDFSBundle\eZ\IO\Handler\DFS
     */
    protected function getDFSIOHandler()
    {
        if ( isset( $this->DFSIOHandler ) )
        {
            $this->DFSIOHandler = new DFS(
                'var/test',
                $this->getDBMock(),
                $this->getFSMock()
            );
        }

        return $this->DFSIOHandler;
    }

    /**
     * @return \eZ\Bundle\EzPublishDFSBundle\eZ\IO\Handler\FSInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFSMock()
    {
        if ( !isset( $this->FSMock ) )
            $this->FSMock = $this->getMock( "BD\\Bundle\\DFSBundle\\eZ\\IO\\Handler\\FSInterface" );

        return $this->FSMock;
    }

    /**
     * @return \eZ\Bundle\EzPublishDFSBundle\eZ\IO\Handler\DBInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDBMock()
    {
        if ( !isset( $this->DBMock ) )
            $this->DBMock = $this->getMock( "BD\\Bundle\\DFSBundle\\eZ\\IO\\Handler\\DBInterface" );

        return $this->DBMock;
    }
}
