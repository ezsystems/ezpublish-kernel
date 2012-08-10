<?php
/**
 * File containing the ImageStorage Gateway
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Image\ImageStorage\Gateway;
use eZ\Publish\SPI\Persistence\Content\VersionInfo,
    eZ\Publish\Core\FieldType\Image\ImageStorage\Gateway;

class LegacyStorage extends Gateway
{
    /**
     * Connection
     *
     * @var mixed
     */
    protected $dbHandler;

    /**
     * Set database handler for this gateway
     *
     * @param mixed $dbHandler
     * @return void
     * @throws \RuntimeException if $dbHandler is not an instance of
     *         {@link \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler}
     */
    public function setConnection( $dbHandler )
    {
        // This obviously violates the Liskov substitution Principle, but with
        // the given class design there is no sane other option. Actually the
        // dbHandler *should* be passed to the constructor, and there should
        // not be the need to post-inject it.
        if ( !$dbHandler instanceof \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler )
        {
            throw new \RuntimeException( "Invalid dbHandler passed" );
        }

        $this->dbHandler = $dbHandler;
    }

    /**
     * Returns the active connection
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler
     * @throws \RuntimeException if no connection has been set, yet.
     */
    protected function getConnection()
    {
        if ( $this->dbHandler === null )
        {
            throw new \RuntimeException( "Missing database connection." );
        }
        return $this->dbHandler;
    }

    /**
     * Returns the node path string of $versionInfo
     *
     * @param VersionInfo $versionInfo
     * @return string
     */
    public function getNodePathString( VersionInfo $versionInfo )
    {
        $connection = $this->getConnection();

        $selectQuery = $connection->createSelectQuery();
        $selectQuery->select( 'path_identification_string' )
            ->from( $connection->quoteTable( 'ezcontentobject_tree' ) )
            ->where(
                $selectQuery->expr->lAnd(
                    $selectQuery->expr->eq(
                        $connection->quoteColumn( 'contentobject_id' ),
                        $selectQuery->bindValue( $versionInfo->contentId )
                    ),
                    $selectQuery->expr->eq(
                        $connection->quoteColumn( 'contentobject_version' ),
                        $selectQuery->bindValue( $versionInfo->versionNo )
                    ),
                    $selectQuery->expr->eq(
                        $connection->quoteColumn( 'node_id' ),
                        $connection->quoteColumn( 'main_node_id' )
                    )
                )
            );
        $statement = $selectQuery->prepare();
        $statement->execute();

        return $statement->fetchColumn();
    }

    /**
     * Stores a reference to the image in $path for $fieldId
     *
     * @param string $path
     * @param mixed $fieldId
     * @return void
     */
    public function storeImageReference( $path, $fieldId )
    {
        $connection = $this->getConnection();

        $insertQuery = $connection->createInsertQuery();
        $insertQuery->insertInto( $connection->quoteTable( 'ezimagefile' ) )
            ->set(
                $connection->quoteColumn( 'contentobject_attribute_id' ),
                $insertQuery->bindValue( $fieldId, null, \PDO::PARAM_INT )
            )->set(
                $connection->quoteColumn( 'filepath' ),
                $insertQuery->bindValue( $path )
            );

        $statement = $insertQuery->prepare();
        $statement->execute();
    }
}

