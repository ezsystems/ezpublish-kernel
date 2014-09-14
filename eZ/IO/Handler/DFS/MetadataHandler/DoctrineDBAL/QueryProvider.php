<?php
namespace EzSystems\DFSIOBundle\eZ\IO\Handler\DFS\MetadataHandler\DoctrineDBAL;

use Doctrine\DBAL\Connection;

class QueryProvider implements QueryProviderInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function createSelectByPath($path)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('*')->from('ezdfsfile')->where( $qb->expr()->eq( 'name_hash', md5( $path ) ) );
        return $qb->getSQL();
    }


    public function createInsert( $path, $mtime )
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->update('ezdfsfile')
            ->set('expired', 1)
            ->set('mtime', '-ABS( mtime )')
            ->where($qb->expr()->eq('f.name', $path));
        return $qb->getSQL();
    }
}
