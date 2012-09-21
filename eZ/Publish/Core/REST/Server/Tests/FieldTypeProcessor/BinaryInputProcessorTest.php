<?php
/**
 * File containing the BinaryInputProcessorTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\FieldTypeProcessor;

use org\bovigo\vfs\vfsStream;

use eZ\Publish\Core\REST\Server\Tests\BaseTest;

abstract class BinaryInputProcessorTest extends BaseTest
{
    const VFS_DIR_NAME = 'tempDir';

    private $vfsRoot;

    public function setUp()
    {
        parent::setUp();

        $this->vfsRoot = vfsStream::setup( self::VFS_DIR_NAME );
    }

    protected function getVfsRoot()
    {
        return $this->vfsRoot;
    }

    protected function getVfsUrl()
    {
        return vfsStream::url( self::VFS_DIR_NAME );
    }

    public function testPreProcessHashMissingKey()
    {
        $processor = $this->getProcessor();

        $inputHash = array( 'foo' => 'bar' );

        $outputHash = $processor->preProcessHash( $inputHash );

        $this->assertEquals( $inputHash, $outputHash );
    }

    public function testPreProcessHash()
    {
        $processor = $this->getProcessor();

        $fileContent = '42';

        $inputHash = array( 'data' => base64_encode( $fileContent ) );

        $outputHash = $processor->preProcessHash( $inputHash );

        $this->assertFalse( isset( $outputHash['data'] ) );
        $this->assertTrue( isset( $outputHash['path'] ) );

        $this->assertTrue(
            file_exists( $outputHash['path'] )
        );

        $this->assertEquals(
            $fileContent,
            file_get_contents( $outputHash['path'] )
        );
    }

    /**
     * Returns the processor under test
     *
     * @return eZ\Publish\Core\REST\Server\FieldTypeProcessor\BinaryInputProcessor
     */
    abstract protected function getProcessor();
}
