<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Image\ImageStorage\Gateway;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
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
     */
    public function storeImageReference($uri, $fieldId)
    {
        // legacy stores the path to the image without a leading /
        $path = $this->redecorator->redecorateFromSource($uri);

        $connection = $this->getConnection();

        $insertQuery = $connection->createInsertQuery();
        $insertQuery->insertInto($connection->quoteTable('ezimagefile'))
            ->set(
                $connection->quoteColumn('contentobject_attribute_id'),
                $insertQuery->bindValue($fieldId, null, \PDO::PARAM_INT)
            )->set(
                $connection->quoteColumn('filepath'),
                $insertQuery->bindValue($path)
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

        if (!$this->canRemoveImageReference($path, $versionNo, $fieldId)) {
            return;
        }

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
            $selectQuery->expr->eq(
                $connection->quoteColumn('filepath'),
                $selectQuery->bindValue($path)
            )
        );

        $statement = $selectQuery->prepare();
        $statement->execute();

        return (int)$statement->fetchColumn();
    }

    public function isImageReferenced(string $uri): bool
    {
        $path = $this->redecorator->redecorateFromSource($uri);

        $connection = $this->getConnection();

        $selectQuery = $connection->createSelectQuery();
        $selectQuery
            ->select(1)
            ->from($connection->quoteTable('ezimagefile'))
            ->where(
                $selectQuery->expr->eq(
                    $connection->quoteColumn('filepath'),
                    $selectQuery->bindValue($path)
                )
            );

        $statement = $selectQuery->prepare();
        $statement->execute();

        return (bool)$statement->fetchColumn();
    }

    /**
     * Checks if image $path can be removed when deleting $versionNo and $fieldId.
     *
     * @param string $path legacy image path (var/storage/images...)
     * @param int $versionNo
     * @param mixed $fieldId
     *
     * @return bool
     */
    protected function canRemoveImageReference($path, $versionNo, $fieldId)
    {
        $connection = $this->getConnection();

        $selectQuery = $connection->createSelectQuery();
        $selectQuery->select(
            $selectQuery->expr->count(
                $connection->quoteColumn('id', 'ezcontentobject_attribute')
            )
        )->from(
            $connection->quoteTable('ezcontentobject_attribute')
        )->innerJoin(
            $connection->quoteTable('ezimagefile'),
            $selectQuery->expr->eq(
                $connection->quoteColumn('contentobject_attribute_id', 'ezimagefile'),
                $connection->quoteColumn('id', 'ezcontentobject_attribute')
            )
        )->where(
            $selectQuery->expr->lAnd(
                $selectQuery->expr->eq(
                    $connection->quoteColumn('contentobject_attribute_id'),
                    $selectQuery->bindValue($fieldId, null, \PDO::PARAM_INT)
                ),
                $selectQuery->expr->neq(
                    $connection->quoteColumn('version'),
                    $selectQuery->bindValue($versionNo, null, \PDO::PARAM_INT)
                ),
                $selectQuery->expr->like(
                    $connection->quoteColumn('filepath'),
                    $selectQuery->bindValue($path . '%')
                )
            )
        );

        $statement = $selectQuery->prepare();
        $statement->execute();

        return (int)$statement->fetchColumn() === 0;
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
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     */
    public function getAllVersionsImageXmlForFieldId(int $fieldId): array
    {
        throw new NotImplementedException('getAllVersionsImageXmlForFieldId is not supported with LegacyStorage gateway, inject DoctrineStorage gateway instead to use it');
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     */
    public function updateImageData(int $fieldId, int $versionNo, string $xml): void
    {
        throw new NotImplementedException('updateImageData is not supported with LegacyStorage gateway, inject DoctrineStorage gateway instead to use it');
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     */
    public function getImagesData(int $offset, int $limit): array
    {
        throw new NotImplementedException('getImagesData is not supported with LegacyStorage gateway, inject DoctrineStorage gateway instead to use it');
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     */
    public function updateImagePath(int $fieldId, string $oldPath, string $newPath): void
    {
        throw new NotImplementedException('updateImagePath is not supported with LegacyStorage gateway, inject DoctrineStorage gateway instead to use it');
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     */
    public function countDistinctImagesData(): int
    {
        throw new NotImplementedException('countDistinctImagesData is not supported with LegacyStorage gateway, inject DoctrineStorage gateway instead to use it');
    }
}
