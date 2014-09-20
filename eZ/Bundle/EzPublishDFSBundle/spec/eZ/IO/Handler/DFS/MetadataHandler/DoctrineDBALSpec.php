<?php

namespace spec\eZ\Bundle\EzPublishDFSBundle\eZ\IO\Handler\DFS\MetadataHandler;

use Doctrine\DBAL;
use eZ\Bundle\EzPublishDFSBundle\eZ\IO\Handler\DFS\MetadataHandler\DoctrineDBAL\QueryProviderInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DoctrineDBALSpec extends ObjectBehavior
{
    function let(DBAL\Connection $connection, QueryProviderInterface $queryProvider)
    {
        $this->beConstructedWith($connection, $queryProvider);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('eZ\Bundle\EzPublishDFSBundle\eZ\IO\Handler\DFS\MetadataHandler\DoctrineDBAL');
    }

    function it_returns_metadata_given_a_file_path_that_exists($connection, QueryProviderInterface $queryProvider, \PDOStatement $stmt)
    {
        $queryProvider->createSelectByPath('file')->willReturn('query');
        $connection->executeQuery('query')->willReturn($stmt);
        $stmt->rowCount()->willReturn(1);
        $stmt->fetch(Argument::any())->willReturn(array('size'=>1, 'mtime'=>1));

        $this->loadMetadata('file')->shouldReturn(array('size'=>1, 'mtime'=>1));
    }

    function it_throws_an_exception_when_loading_metadata_given_a_file_path_that_does_not_exist($connection, QueryProviderInterface $queryProvider, \PDOStatement $stmt)
    {
        $queryProvider->createSelectByPath('file')->willReturn('query');
        $connection->executeQuery('query')->willReturn($stmt);
        $stmt->rowCount()->willReturn(0);

        $this->shouldThrow('eZ\Publish\Core\Base\Exceptions\NotFoundException')->duringLoadMetadata('file');
    }

    function it_creates_a_new_metadata_record()
    {

    }

    function it_throws_an_exception_when_creating_a_file_that_exists()
    {

    }

    function it_marks_an_existing_file_as_expired()
    {

    }

    function it_checks_if_a_file_exists()
    {

    }

    function it_checks_if_a_file_does_not_exist()
    {

    }

    function it_renames_a_file()
    {

    }

    function it_throws_an_exception_renaming_a_file_that_does_not_exist()
    {

    }
}
