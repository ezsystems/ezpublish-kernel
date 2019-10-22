<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Image\ImageStorage\Gateway;

use eZ\Publish\Core\IO\UrlRedecoratorInterface;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\Core\FieldType\Image\ImageStorage\Gateway;

/**
 * @deprecated since 6.11. Use {@see \eZ\Publish\Core\FieldType\Image\ImageStorage\Gateway\DoctrineStorage} instead.
 */
class LegacyStorage extends Gateway
{
    /** @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler */
    protected $dbHandler;

    /**
     * Maps database field names to property names.
     *
     * @var array
     */
    protected $fieldNameMap = [
        'id' => 'fieldId',
        'version' => 'versionNo',
        'language_code' => 'languageCode',
        'path_identification_string' => 'nodePathString',
        'data_string' => 'xml',
    ];

    /** @var \eZ\Publish\Core\IO\UrlRedecoratorInterface */
    private $redecorator;

    public function __construct(UrlRedecoratorInterface $redecorator, DatabaseHandler $dbHandler)
    {
        @trigger_error(
            sprintf('%s is deprecated, use %s instead', self::class, DoctrineStorage::class),
            E_USER_DEPRECATED
        );
        $this->redecorator = $redecorator;
        $this->dbHandler = $dbHandler;
    }

    /**
     * Returns the active connection.
     *
     * @return \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected function getConnection()
    {
        return $this->dbHandler;
    }

    /**
     * Returns the node path string of $versionInfo.
     *
     * @param VersionInfo $versionInfo
     *
     * @return string
     */
    public function getNodePathString(VersionInfo $versionInfo)
    {
        $connection = $this->getConnection();

        $selectQuery = $connection->createSelectQuery();
        $selectQuery->select('path_identification_string')
            ->from($connection->quoteTable('ezcontentobject_tree'))
            ->where(
                $selectQuery->expr->lAnd(
                    $selectQuery->expr->eq(
                        $connection->quoteColumn('contentobject_id'),
                        $selectQuery->bindValue($versionInfo->contentInfo->id)
                    ),
                    $selectQuery->expr->eq(
                        $connection->quoteColumn('contentobject_version'),
                        $selectQuery->bindValue($versionInfo->versionNo)
                    ),
                    $selectQuery->expr->eq(
                        $connection->quoteColumn('node_id'),
                        $connection->quoteColumn('main_node_id')
                    )
                )
            );
        $statement = $selectQuery->prepare();
        $statement->execute();

        return $statement->fetchColumn();
    }

    /**
     * Stores a reference to the image in $path for $fieldId.
     *
     * @param string $uri File IO uri (not legacy)
     * @param mixed $fieldId
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     *
     * @throws \eZ\Publish\Core\IO\Exception\InvalidBinaryFileIdException
     */
    public function storeImageReference($uri, $fieldId, VersionInfo $versionInfo)
    {
        // legacy stores the path to the image without a leading /
        $path = $this->redecorator->redecorateFromSource($uri);

        if ($this->imageReferenceExistsForVersion($fieldId, $versionInfo)) {
            return $this->updateImageReferenceForVersion($path, $fieldId, $versionInfo);
        }

        $connection = $this->getConnection();

        $insertQuery = $connection->createInsertQuery();
        $insertQuery->insertInto($connection->quoteTable('ezimagefile'))
            ->set(
                $connection->quoteColumn('contentobject_attribute_id'),
                $insertQuery->bindValue($fieldId, null, \PDO::PARAM_INT)
            )->set(
                $connection->quoteColumn('filepath'),
                $insertQuery->bindValue($path)
            )->set(
                $connection->quoteColumn('version'),
                $insertQuery->bindValue($versionInfo->versionNo, \PDO::PARAM_INT)
            );

        $statement = $insertQuery->prepare();
        $statement->execute();
    }

    /**
     * Returns a the XML content stored for the given $fieldIds.
     *
     * @param int $versionNo
     * @param array $fieldIds
     *
     * @return array
     */
    public function getXmlForImages($versionNo, array $fieldIds)
    {
        $connection = $this->getConnection();

        $selectQuery = $connection->createSelectQuery();
        $selectQuery->select(
            $connection->quoteColumn('id', 'ezcontentobject_attribute'),
            $connection->quoteColumn('data_text', 'ezcontentobject_attribute')
        )->from(
            $connection->quoteTable('ezcontentobject_attribute')
        )->where(
            $selectQuery->expr->lAnd(
                $selectQuery->expr->eq(
                    $connection->quoteColumn('version', 'ezcontentobject_attribute'),
                    $selectQuery->bindValue($versionNo, null, \PDO::PARAM_INT)
                ),
                $selectQuery->expr->in(
                    $connection->quoteColumn('id', 'ezcontentobject_attribute'),
                    $fieldIds
                )
            )
        );

        $statement = $selectQuery->prepare();
        $statement->execute();

        $fieldLookup = [];
        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $fieldLookup[$row['id']] = $row['data_text'];
        }

        return $fieldLookup;
    }

    /**
     * Removes all references from $fieldId to a path that starts with $path.
     *
     * @param string $uri File IO uri (not legacy)
     * @param int $versionNo
     * @param mixed $fieldId
     */
    public function removeImageReferences($uri, $versionNo, $fieldId)
    {
        $path = $this->redecorator->redecorateFromSource($uri);

        $connection = $this->getConnection();

        $deleteQuery = $connection->createDeleteQuery();
        $deleteQuery->deleteFrom(
            $connection->quoteTable('ezimagefile')
        )->where(
            $deleteQuery->expr->lAnd(
                $deleteQuery->expr->eq(
                    $connection->quoteColumn('contentobject_attribute_id'),
                    $deleteQuery->bindValue($fieldId, null, \PDO::PARAM_INT)
                ),
                $deleteQuery->expr->eq(
                    $connection->quoteColumn('version'),
                    $deleteQuery->bindValue($versionNo, null, \PDO::PARAM_INT)
                ),
                $deleteQuery->expr->like(
                    $connection->quoteColumn('filepath'),
                    $deleteQuery->bindValue($path . '%')
                )
            )
        );

        $statement = $deleteQuery->prepare();
        $statement->execute();
    }

    /**
     * Returns the number of recorded references to the given $path.
     *
     * @param string $uri File IO uri (not legacy)
     *
     * @return int
     */
    public function countImageReferences($uri)
    {
        $path = $this->redecorator->redecorateFromSource($uri);

        $connection = $this->getConnection();

        $selectQuery = $connection->createSelectQuery();
        $selectQuery->select(
            $selectQuery->expr->count(
                $connection->quoteColumn('id')
            )
        )->from(
            $connection->quoteTable('ezimagefile')
        )->where(
            $selectQuery->expr->like(
                $connection->quoteColumn('filepath'),
                $selectQuery->bindValue($path . '%')
            )
        );

        $statement = $selectQuery->prepare();
        $statement->execute();

        return (int)$statement->fetchColumn();
    }

    public function extractFilesFromXml($xml)
    {
        if (empty($xml)) {
            // Empty image value
            return null;
        }

        $files = [];

        $dom = new \DOMDocument();
        $dom->loadXml($xml);
        if ($dom->documentElement->hasAttribute('dirpath')) {
            $url = $dom->documentElement->getAttribute('url');
            if (empty($url)) {
                return null;
            }

            $files['original'] = $this->redecorator->redecorateFromTarget($url);
            /** @var \DOMNode $childNode */
            foreach ($dom->documentElement->childNodes as $childNode) {
                if ($childNode->nodeName != 'alias') {
                    continue;
                }

                $files[$childNode->getAttribute('name')] = $this->redecorator->redecorateFromTarget(
                    $childNode->getAttribute('url')
                );
            }

            return $files;
        }

        return null;
    }

    /**
     * Checks whether image reference for given version already exists.
     *
     * @param int $fieldId
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     *
     * @return bool
     */
    private function imageReferenceExistsForVersion(int $fieldId, VersionInfo $versionInfo): bool
    {
        $connection = $this->getConnection();

        $selectQuery = $connection->createSelectQuery();
        $selectQuery
            ->select(
                $connection->quoteIdentifier('id')
            )->from(
                $connection->quoteTable('ezimagefile')
            )->where(
                $selectQuery->expr->lAnd(
                    $selectQuery->expr->eq(
                        $connection->quoteIdentifier('contentobject_attribute_id'),
                        $selectQuery->bindValue($fieldId, null, \PDO::PARAM_INT)
                    ),
                    $selectQuery->expr->eq(
                        $connection->quoteIdentifier('version'),
                        $selectQuery->bindValue($versionInfo->versionNo, null, \PDO::PARAM_INT)
                    )
                )
            );

        $statement = $selectQuery->prepare();
        $statement->execute();

        return (int) $statement->fetchColumn() !== 0;
    }

    /**
     * Updates existing image reference for given version.
     *
     * @param string $uri
     * @param int $fieldId
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     */
    private function updateImageReferenceForVersion(string $uri, int $fieldId, VersionInfo $versionInfo): void
    {
        $connection = $this->getConnection();

        $updateQuery = $connection->createUpdateQuery();
        $updateQuery
            ->update(
                $connection->quoteIdentifier('ezimagefile')
            )->set(
                $connection->quoteIdentifier('filepath'),
                $updateQuery->bindValue($uri, null)
            )->where(
                $updateQuery->expr->lAnd(
                    $updateQuery->expr->eq(
                        $connection->quoteIdentifier('contentobject_attribute_id'),
                        $updateQuery->bindValue($fieldId, null, \PDO::PARAM_INT)
                    ),
                    $updateQuery->expr->eq(
                        $connection->quoteIdentifier('version'),
                        $updateQuery->bindValue($versionInfo->versionNo, null, \PDO::PARAM_INT)
                    )
                )
            );

        $statement = $updateQuery->prepare();
        $statement->execute();
    }
}
