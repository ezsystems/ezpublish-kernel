<?php
/**
 * File containing the BinaryInputProcessorTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\FieldTypeProcessor;

use eZ\Publish\Core\REST\Server\Tests\BaseTest;

abstract class BinaryInputProcessorTest extends BaseTest
{
    private $tempDir;

    public function tearDown()
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $this->getTempDir(),
                \FileSystemIterator::KEY_AS_PATHNAME | \FileSystemIterator::SKIP_DOTS | \ FilesystemIterator::CURRENT_AS_FILEINFO
            ),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        parent::tearDown();
    }

    /**
     * Returns a temp directory path and creates it, if necessary
     *
     * @return string The directory path
     */
    protected function getTempDir()
    {
        if ( !isset( $this->tempDir ) )
        {
            $tempFile = tempnam(
                sys_get_temp_dir(),
                'eZ_REST_BinaryInput'
            );

            unlink( $tempFile );

            $this->tempDir = $tempFile;

            mkdir( $this->tempDir );
        }

        return $this->tempDir;
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
     * @return \eZ\Publish\Core\REST\Server\FieldTypeProcessor\BinaryInputProcessor
     */
    abstract protected function getProcessor();
}
