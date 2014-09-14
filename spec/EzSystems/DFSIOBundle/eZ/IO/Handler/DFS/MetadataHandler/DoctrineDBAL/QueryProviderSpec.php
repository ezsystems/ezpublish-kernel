<?php

namespace spec\EzSystems\DFSIOBundle\eZ\IO\Handler\DFS\MetadataHandler\DoctrineDBAL;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class QueryProviderSpec extends ObjectBehavior
{
    function let(\Doctrine\DBAL\Connection $connection)
    {
        $this->beConstructedWith($connection);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('EzSystems\DFSIOBundle\eZ\IO\Handler\DFS\MetadataHandler\DoctrineDBAL\QueryProvider');
    }
}
