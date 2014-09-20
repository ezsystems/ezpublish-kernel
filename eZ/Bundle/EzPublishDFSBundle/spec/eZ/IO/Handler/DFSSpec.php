<?php

namespace spec\eZ\Bundle\EzPublishDFSBundle\eZ\IO\Handler;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\SPI\IO\BinaryFile;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct;
use eZ\Publish\SPI\IO\BinaryFileUpdateStruct;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use eZ\Bundle\EzPublishDFSBundle\eZ\IO\Handler\DFS\MetadataHandler;
use eZ\Bundle\EzPublishDFSBundle\eZ\IO\Handler\DFS\BinaryDataHandler;

class DFSSpec extends ObjectBehavior
{
    public function let(MetadataHandler $metadataHandler, BinaryDataHandler $binaryDataHandler)
    {
        $this->beConstructedWith('prefix/', $metadataHandler, $binaryDataHandler);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\eZ\Bundle\EzPublishDFSBundle\eZ\IO\Handler\DFS');
    }

    function it_creates_a_file(MetadataHandler $metadataHandler, BinaryDataHandler $binaryDataHandler)
    {
        $createStruct = $this->createBinaryFileCreateStruct();

        $binaryDataHandler
            ->createFromStream( $this->getPathWithPrefix(), $createStruct->getInputStream() )
            ->shouldBeCalled();
        $metadataHandler
            ->insert($this->getPathWithPrefix(), $createStruct->mtime)
            ->shouldBeCalled();

        $this->create($createStruct);
    }

    public function it_throws_an_exception_on_create_with_an_existing_file(MetadataHandler $metadataHandler, BinaryDataHandler $binaryDataHandler)
    {
        $createStruct = $this->createBinaryFileCreateStruct();

        $binaryDataHandler
            ->createFromStream($this->getPathWithPrefix(), $createStruct->getInputStream())
            ->shouldBeCalled();
        $metadataHandler
            ->insert($this->getPathWithPrefix(), $createStruct->mtime)
            ->shouldBeCalled();
        $this->create($createStruct);
    }

    function it_deletes_a_file_that_exists(MetadataHandler $metadataHandler, BinaryDataHandler $binaryDataHandler)
    {
        $binaryDataHandler->delete($this->getPathWithPrefix())->shouldBeCalled();
        $metadataHandler->delete($this->getPathWithPrefix())->shouldBeCalled();
        $this->delete($this->getPathWithoutPrefix());
    }

    function it_throws_an_exception_on_delete_with_a_file_that_does_not_exist(MetadataHandler $metadataHandler, BinaryDataHandler $binaryDataHandler)
    {
        $e = $this->createNotFoundException();
        $metadataHandler->delete($this->getPathWithPrefix())->willThrow($e);
        $binaryDataHandler->delete(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow($e)
            ->duringDelete($this->getPathWithoutPrefix());
    }

    function it_renames_a_file_given_a_struct_without_a_stream(MetadataHandler $metadataHandler, BinaryDataHandler $binaryDataHandler)
    {
        $updateStruct = $this->createBinaryFileUpdateStruct();
        $updateStruct->id = $this->getNewPathWithoutPrefix();
        $updateStruct->setInputStream(null);

        $metadataHandler
            ->exists($this->getPathWithPrefix())
            ->willReturn(true);
        $metadataHandler
            ->exists($this->getNewPathWithPrefix())
            ->willReturn(false);
        $metadataHandler
            ->rename($this->getPathWithPrefix(), $this->getNewPathWithPrefix())
            ->shouldBeCalled();
        $binaryDataHandler
            ->rename($this->getPathWithPrefix(), $this->getNewPathWithPrefix())
            ->shouldBeCalled();
        $metadataHandler
            ->loadMetadata($this->getNewPathWithPrefix())
            // @todo custom thing ->willReturnBinaryFileArray()
            ->willReturn(array('mtime' => 12345, 'size' => 123));

        $this
            ->update($this->getPathWithoutPrefix(), $updateStruct)
            // @todo ->shouldReturnBinaryFile()
            ->shouldReturnAnInstanceOf('eZ\Publish\SPI\IO\BinaryFile');
    }

    public function it_updates_the_content_of_a_file_given_a_struct_with_a_stream(MetadataHandler $metadataHandler, BinaryDataHandler $binaryDataHandler)
    {
        $updateStruct = $this->createBinaryFileUpdateStruct();

        $metadataHandler
            ->exists($this->getPathWithPrefix())
            ->willReturn(true);
        $metadataHandler
            ->rename($this->getPathWithPrefix(), $this->getNewPathWithPrefix())
            ->shouldNotBeCalled();
        $binaryDataHandler
            ->rename($this->getPathWithPrefix(), $this->getNewPathWithPrefix())
            ->shouldNotBeCalled();
        $binaryDataHandler
            ->updateFileContents($this->getPathWithPrefix(), $updateStruct->getInputStream())
            ->shouldBeCalled();
        $metadataHandler
            ->loadMetadata($this->getPathWithPrefix())
            // @todo custom thing ->willReturnBinaryFileArray()
            ->willReturn(array('mtime' => 12345, 'size' => 123));

        $this
            ->update($this->getPathWithoutPrefix(), $updateStruct)
            // @todo ->shouldReturnBinaryFile()
            ->shouldReturnAnInstanceOf('eZ\Publish\SPI\IO\BinaryFile');
    }

    function it_returns_true_on_exists_if_the_file_exists(MetadataHandler $metadataHandler)
    {
        $metadataHandler->exists($this->getPathWithPrefix())->willReturn(true);
        $this->exists($this->getPathWithoutPrefix())->shouldReturn(true);
    }

    function it_returns_false_on_exists_if_the_file_does_not_exist(MetadataHandler $metadataHandler)
    {
        $metadataHandler->exists($this->getPathWithPrefix())->willReturn(false);
        $this->exists($this->getPathWithoutPrefix())->shouldReturn(false);
    }

    function it_loads_an_existing_file(MetadataHandler $metadataHandler)
    {
        $metadataHandler->loadMetadata($this->getPathWithPrefix())->willReturn(array('mtime' => 12345, 'size' => 123));
        $this->load($this->getPathWithoutPrefix())->shouldReturnAnInstanceOf('eZ\Publish\SPI\IO\BinaryFile');
    }

    function it_throws_an_exception_when_loading_a_file_that_does_not_exist(MetadataHandler $metadataHandler)
    {
        $e = $this->createNotFoundException();
        $metadataHandler->loadMetadata($this->getPathWithPrefix())->willThrow($e);
        $this->shouldThrow('eZ\Publish\Core\Base\Exceptions\NotFoundException')->duringLoad($this->getPathWithoutPrefix());
    }

    function it_returns_a_file_resource_given_a_path(MetadataHandler $metadataHandler, BinaryDataHandler $binaryDataHandler)
    {
        $resource = fopen('php://temp', 'r');
        $metadataHandler->exists($this->getPathWithPrefix())->willReturn(true);
        $binaryDataHandler->getFileResource($this->getPathWithPrefix())->willReturn($resource);

        $this->getFileResource($this->getPathWithoutPrefix())->shouldReturn($resource);
    }

    function it_throws_an_exception_when_asked_for_a_resource_given_a_file_path_that_does_not_exist(MetadataHandler $metadataHandler)
    {
        $metadataHandler->exists($this->getPathWithPrefix())->willReturn(false);
        $this->shouldThrow('eZ\Publish\Core\Base\Exceptions\NotFoundException')->duringGetFileResource($this->getPathWithoutPrefix());
    }

    function it_returns_the_contents_of_an_existing_file(BinaryDataHandler $binaryDataHandler)
    {
        $contents = 'This is not content';
        $binaryDataHandler->getFileContents($this->getPathWithPrefix())->willReturn($contents);
        $this->getFileContents($this->getPathWithoutPrefix())->shouldReturn($contents);
    }

    function it_returns_an_internal_path()
    {
        $this->getInternalPath($this->getPathWithoutPrefix())->shouldReturn($this->getPathWithPrefix());
    }

    function it_returns_an_external_path()
    {
        $this->getExternalPath($this->getPathWithPrefix())->shouldReturn($this->getPathWithoutPrefix());
    }

    private function createBinaryFile()
    {
        $binaryFile = new BinaryFile();
        $binaryFile->mtime = time();
        return $binaryFile;
    }

    /**
     * @return BinaryFileCreateStruct
     */
    private function createBinaryFileCreateStruct()
    {
        $createStruct = new BinaryFileCreateStruct();
        $createStruct->setInputStream(fopen('php://temp', 'r'));
        $createStruct->mtime = time();
        $createStruct->id = $this->getPathWithoutPrefix();
        return $createStruct;
    }

    /**
     * @return BinaryFileUpdateStruct
     */
    private function createBinaryFileUpdateStruct()
    {
        $updateStruct = new BinaryFileUpdateStruct();
        $updateStruct->setInputStream(fopen('php://temp', 'r'));
        $updateStruct->mtime = time();
        $updateStruct->id = $this->getPathWithoutPrefix();
        return $updateStruct;
    }

    /**
     * @return NotFoundException
     */
    private function createNotFoundException()
    {
        return new NotFoundException('w', ' w');
    }

    private function getPathWithoutPrefix()
    {
        return 'file';
    }

    private function getPathWithPrefix()
    {
        return 'prefix/file';
    }

    private function getNewPathWithoutPrefix()
    {
        return 'newfile';
    }

    private function getNewPathWithPrefix()
    {
        return 'prefix/newfile';
    }
}
