<?php
/**
 * File containing the DoctrineDBAL MetadataHandler class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace EzSystems\DFSIOBundle\eZ\IO\Handler\DFS\MetadataHandler;

use EzSystems\DFSIOBundle\eZ\IO\Handler\DFS\MetadataHandler;
use Doctrine\DBAL\Connection;

class DoctrineDBAL implements MetadataHandler
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $db;

    public function __construct( Connection $db )
    {
        $this->db = $db;
    }

    /**
     * Inserts a new reference to file $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If a file $path already exists
     *
     * @param string  $path
     * @param integer $mtime
     */
    public function insert( $path, $mtime )
    {
        $this->db->prepare( "INSERT INTO ezdfsfile ()" )
    }

    /**
     * Deletes file $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $path is not found
     *
     * @param string $path
     */
    public function delete( $path )
    {
        $qb = $this->db->createQueryBuilder();
        $qb->update( 'ezdfsfile')
           ->set( 'expired', 1 )
           ->set( '-ABS( mtime )')
           ->where( $qb->expr()->eq( 'f.name', $path ) );
        try
        {
            $qb->execute();
        }
        catch ( DBALException $e )
        {
            throw $e;
        }
    }

    /**
     * Loads and returns metadata for $path
     *
     * @param string $path
     *
     * @return array A hash with metadata for $path. Keys: mtime, size.
     */
    public function loadMetadata( $path )
    {
        $qb = $this->db->createQueryBuilder();

        $qb->select( '*' )
           ->from( 'ezdfsfile' )
           ->where( $qb->expr()->eq( 'name_hash', md5( $path ) )
    }

    /**
     * Checks if a file $path exists
     *
     * @param string $path
     *
     * @return bool
     */
    public function exists( $path )
    {
        // TODO: Implement exists() method.
    }

    /**
     * Renames file $fromPath to $toPath
     *
     * @param $fromPath
     * @param $toPath
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If $toPath already exists
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $fromPath does not exist
     */
    public function rename( $fromPath, $toPath )
    {
        // TODO: Implement rename() method.
    }

}
