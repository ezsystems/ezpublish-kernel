<?php
/**
 * File containing the DoctrineDBAL MetadataHandler class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace EzSystems\DFSIOBundle\eZ\IO\Handler\DFS\MetadataHandler;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use EzSystems\DFSIOBundle\eZ\IO\Handler\DFS\MetadataHandler;

class DoctrineDBAL implements MetadataHandler
{
    /**
     * @var DoctrineDBAL\QueryRunnerInterface
     */
    private $queryRunner;

    /**
     * @var DoctrineDBAL\QueryProviderInterface
     */
    private $queryProvider;

    public function __construct( DoctrineDBAL\QueryRunnerInterface $queryRunner, DoctrineDBAL\QueryProviderInterface $queryProvider)
    {
        $this->queryRunner = $queryRunner;
        $this->queryProvider = $queryProvider;
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
        try {
            $this->queryRunner->runInsertOne(
                $this->queryProvider->insertQuery($path, $mtime)
            );
        } catch (\Exception $e) {

        }
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
     * @param string $path
     * @return array A hash with metadata for $path. Keys: mtime, size.
     * @throws NotFoundException if no row is found for $path
     */
    public function loadMetadata($path)
    {
        try {
            $this->queryRunner->selectOne(
                $this->queryProvider->createSelectByPath($path)
            );
        } catch (\Exception $e) {
            throw new NotFoundException('file', $path);
        }
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
