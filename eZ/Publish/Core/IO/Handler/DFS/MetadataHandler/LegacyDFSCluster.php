<?php
/**
 * File containing the DoctrineDBAL MetadataHandler class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\IO\Handler\DFS\MetadataHandler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\IO\Handler\DFS\MetadataHandler;

/**
 * @todo Describe
 * @todo Rename to LegacyStorage ?
 */
class LegacyDFSCluster implements MetadataHandler
{
    /** @var Connection */
    private $db;

    public function __construct(Connection $connection)
    {
        $this->db = $connection;
    }

    /**
     * Inserts a new reference to file $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If a file $path already exists
     *
     * @param string  $path
     * @param integer $mtime
     */
    public function insert($path, $size)
    {
        $nameTrunk = $path;
        try {
            /**
             * @todo what might go wrong here ? Can another process be trying to insert the same image ?
             *       what happens if somebody did ?
             **/
            $stmt = $this->db->prepare(<<<SQL
INSERT INTO ezdfsfile
  (name, name_hash, name_trunk, mtime, size, scope, datatype)
  VALUES (:name, :name_hash, :name_trunk, :mtime, :size, :scope, :datatype)
ON DUPLICATE KEY UPDATE
  datatype=VALUES(datatype), scope=VALUES(scope), size=VALUES(size),
  mtime=VALUES(mtime), expired=VALUES(expired)
SQL
            );
            $stmt->bindValue('name', $path);
            $stmt->bindValue('name_hash', md5($path));
            $stmt->bindValue('name_trunk', $nameTrunk);
            $stmt->bindValue('mtime', time());
            $stmt->bindValue('size', $size);
            $stmt->bindValue('scope', '');
            $stmt->bindValue('datatype', '');
            $stmt->execute();
        } catch (DBALException $e) {
            throw $e;
        }

        if ($stmt->rowCount() == 0) {
            throw new \Exception("@TODO: unexpected rowCount " . $stmt->rowCount());
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
        try
        {
            /**
             * @todo delete or expire ? legacy_mode option ?
             */
            $stmt = $this->db->prepare('DELETE FROM ezdfsfile WHERE name_hash LIKE :name_hash');
            $stmt->bindValue('name_hash', md5($path));
            $stmt->execute();
        }
        catch ( DBALException $e )
        {
            throw $e;
        }

        if ($stmt->rowCount() != 1) {
            throw new \Exception("@todo");
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
            $stmt = $this->db->prepare('SELECT * FROM ezdfsfile WHERE name_hash LIKE ?');
            $stmt->bindValue(1, md5($path));
            $stmt->execute();
        } catch (\Exception $e) {
            throw new NotFoundException('file', $path);
        }

        if ($stmt->rowCount() != 1) {
            throw new \Exception("@todo");
        }

        return $stmt->fetch(\PDO::FETCH_ASSOC);
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
        try {
            $stmt = $this->db->prepare('SELECT name FROM ezdfsfile WHERE name_hash LIKE ?');
            $stmt->bindValue(1, md5($path));
            $stmt->execute();
        } catch (\Exception $e) {
            throw new NotFoundException('file', $path);
        }
        return ($stmt->rowCount() == 1);
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
    public function rename($fromPath, $toPath)
    {
        try {
            $stmt = $this->db->prepare('UPDATE ezdfsfile SET name = ?, name_hash = ? WHERE name_hash LIKE ?');
            $stmt->bindValue(1, $toPath);
            $stmt->bindValue(2, md5($toPath));
            $stmt->bindValue(3, $fromPath);
            $stmt->execute();
        } catch (\Exception $e) {
            throw new \Exception("@todo");
        }

        if ($stmt->rowCount()!=1) {
            throw new NotFoundException("@todo", $fromPath);
        }
    }
}
