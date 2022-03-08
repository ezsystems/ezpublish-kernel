<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Image\ImageStorage\Gateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ParameterType;
use DOMDocument;
use eZ\Publish\Core\FieldType\Image\ImageStorage\Gateway;
use eZ\Publish\Core\IO\UrlRedecoratorInterface;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use PDO;

/**
 * Image Field Type external storage DoctrineStorage gateway.
 */
class DoctrineStorage extends Gateway
{
    const IMAGE_FILE_TABLE = 'ezimagefile';

    /** @var \Doctrine\DBAL\Connection */
    protected $connection;

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

    public function __construct(UrlRedecoratorInterface $redecorator, Connection $connection)
    {
        $this->redecorator = $redecorator;
        $this->connection = $connection;
    }

    /**
     * Return the node path string of $versionInfo.
     *
     * @return string
     */
    public function getNodePathString(VersionInfo $versionInfo)
    {
        $selectQuery = $this->connection->createQueryBuilder();
        $selectQuery
            ->select($this->connection->quoteIdentifier('path_identification_string'))
            ->from($this->connection->quoteIdentifier('ezcontentobject_tree'))
            ->where(
                $selectQuery->expr()->andX(
                    $selectQuery->expr()->eq(
                        $this->connection->quoteIdentifier('contentobject_id'),
                        ':contentObjectId'
                    ),
                    $selectQuery->expr()->eq(
                        $this->connection->quoteIdentifier('contentobject_version'),
                        ':versionNo'
                    ),
                    $selectQuery->expr()->eq(
                        $this->connection->quoteIdentifier('node_id'),
                        $this->connection->quoteIdentifier('main_node_id')
                    )
                )
            )
            ->setParameter(':contentObjectId', $versionInfo->contentInfo->id, PDO::PARAM_INT)
            ->setParameter(':versionNo', $versionInfo->versionNo, PDO::PARAM_INT)
        ;

        $statement = $selectQuery->execute();

        return $statement->fetchColumn();
    }

    /**
     * Store a reference to the image in $path for $fieldId.
     *
     * @param string $uri File IO uri (not legacy)
     * @param int $fieldId
     */
    public function storeImageReference($uri, $fieldId)
    {
        // legacy stores the path to the image without a leading /
        $path = $this->redecorator->redecorateFromSource($uri);

        $insertQuery = $this->connection->createQueryBuilder();
        $insertQuery
            ->insert($this->connection->quoteIdentifier(self::IMAGE_FILE_TABLE))
            ->values(
                [
                    $this->connection->quoteIdentifier('contentobject_attribute_id') => ':fieldId',
                    $this->connection->quoteIdentifier('filepath') => ':path',
                ]
            )
            ->setParameter(':fieldId', $fieldId, PDO::PARAM_INT)
            ->setParameter(':path', $path)
        ;

        $insertQuery->execute();
    }

    /**
     * Return an XML content stored for the given $fieldIds.
     *
     * @param int $versionNo
     *
     * @return array
     */
    public function getXmlForImages($versionNo, array $fieldIds)
    {
        $selectQuery = $this->connection->createQueryBuilder();
        $selectQuery
            ->select(
                $this->connection->quoteIdentifier('attr.id'),
                $this->connection->quoteIdentifier('attr.data_text')
            )
            ->from($this->connection->quoteIdentifier('ezcontentobject_attribute'), 'attr')
            ->where(
                $selectQuery->expr()->andX(
                    $selectQuery->expr()->eq(
                        $this->connection->quoteIdentifier('attr.version'),
                        ':versionNo'
                    ),
                    $selectQuery->expr()->in(
                        $this->connection->quoteIdentifier('attr.id'),
                        ':fieldIds'
                    )
                )
            )
            ->setParameter(':versionNo', $versionNo, PDO::PARAM_INT)
            ->setParameter(':fieldIds', $fieldIds, Connection::PARAM_INT_ARRAY)
        ;

        $statement = $selectQuery->execute();

        $fieldLookup = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $fieldLookup[$row['id']] = $row['data_text'];
        }

        return $fieldLookup;
    }

    public function getAllVersionsImageXmlForFieldId(int $fieldId): array
    {
        $selectQuery = $this->connection->createQueryBuilder();
        $selectQuery
            ->select(
                'field.id',
                'field.version',
                'field.data_text'
            )
            ->from($this->connection->quoteIdentifier('ezcontentobject_attribute'), 'field')
            ->where(
                $selectQuery->expr()->eq(
                    $this->connection->quoteIdentifier('id'),
                    ':field_id'
                )
            )
            ->setParameter(':field_id', $fieldId, PDO::PARAM_INT)
        ;

        $statement = $selectQuery->execute();

        $fieldLookup = [];
        foreach ($statement->fetchAllAssociative() as $row) {
            $fieldLookup[$row['id']] = [
                'version' => $row['version'],
                'data_text' => $row['data_text'],
            ];
        }

        return $fieldLookup;
    }

    /**
     * Remove all references from $fieldId to a path that starts with $path.
     *
     * @param string $uri File IO uri (not legacy)
     * @param int $versionNo
     * @param int $fieldId
     *
     * @throws \eZ\Publish\Core\IO\Exception\InvalidBinaryFileIdException
     */
    public function removeImageReferences($uri, $versionNo, $fieldId): void
    {
        if (!$this->canRemoveImageReference($uri, $versionNo, $fieldId)) {
            return;
        }

        $path = $this->redecorator->redecorateFromSource($uri);

        $deleteQuery = $this->connection->createQueryBuilder();
        $deleteQuery
            ->delete($this->connection->quoteIdentifier(self::IMAGE_FILE_TABLE))
            ->where(
                $deleteQuery->expr()->andX(
                    $deleteQuery->expr()->eq(
                        $this->connection->quoteIdentifier('contentobject_attribute_id'),
                        ':fieldId'
                    ),
                    $deleteQuery->expr()->like(
                        $this->connection->quoteIdentifier('filepath'),
                        ':likePath'
                    )
                )
            )
            ->setParameter(':fieldId', $fieldId, PDO::PARAM_INT)
            ->setParameter(':likePath', $path . '%')
        ;

        $deleteQuery->execute();
    }

    /**
     * Return the number of recorded references to the given $path.
     *
     * @param string $uri File IO uri (not legacy)
     *
     * @return int
     */
    public function countImageReferences($uri)
    {
        $path = $this->redecorator->redecorateFromSource($uri);

        $selectQuery = $this->connection->createQueryBuilder();
        $selectQuery
            ->select('COUNT(' . $this->connection->quoteIdentifier('id') . ')')
            ->from($this->connection->quoteIdentifier(self::IMAGE_FILE_TABLE))
            ->where(
                $selectQuery->expr()->eq(
                    $this->connection->quoteIdentifier('filepath'),
                    ':filepath'
                )
            )
            ->setParameter(':filepath', $path)
        ;

        $statement = $selectQuery->execute();

        return (int) $statement->fetchColumn();
    }

    public function isImageReferenced(string $uri): bool
    {
        $path = $this->redecorator->redecorateFromSource($uri);

        $selectQuery = $this->connection->createQueryBuilder();
        $selectQuery
            ->select(1)
            ->from($this->connection->quoteIdentifier(self::IMAGE_FILE_TABLE))
            ->where(
                $selectQuery->expr()->eq(
                    $this->connection->quoteIdentifier('filepath'),
                    ':likePath'
                )
            )
            ->setParameter(':likePath', $path)
        ;

        $statement = $selectQuery->execute();

        return (bool)$statement->fetchOne();
    }

    public function countDistinctImagesData(): int
    {
        $selectQuery = $this->connection->createQueryBuilder();
        $selectQuery
            ->select($this->connection->getDatabasePlatform()->getCountExpression('id'))
            ->from($this->connection->quoteIdentifier(self::IMAGE_FILE_TABLE))
        ;

        $statement = $selectQuery->execute();

        return (int) $statement->fetchOne();
    }

    /**
     * Check if image $path can be removed when deleting $versionNo and $fieldId.
     *
     * @param string $path legacy image path (var/storage/images...)
     * @param int $versionNo
     * @param int $fieldId
     */
    protected function canRemoveImageReference($path, $versionNo, $fieldId): bool
    {
        $selectQuery = $this->connection->createQueryBuilder();
        $expressionBuilder = $selectQuery->expr();
        $selectQuery
            ->select('attr.data_text')
            ->from($this->connection->quoteIdentifier('ezcontentobject_attribute'), 'attr')
            ->innerJoin(
                'attr',
                $this->connection->quoteIdentifier(self::IMAGE_FILE_TABLE),
                'img',
                $expressionBuilder->eq(
                    $this->connection->quoteIdentifier('img.contentobject_attribute_id'),
                    $this->connection->quoteIdentifier('attr.id')
                )
            )
            ->where(
                $expressionBuilder->eq(
                    $this->connection->quoteIdentifier('contentobject_attribute_id'),
                    ':fieldId'
                )
            )
            ->andWhere(
                $expressionBuilder->neq(
                    $this->connection->quoteIdentifier('version'),
                    ':versionNo'
                )
            )
            ->setParameter(':fieldId', $fieldId, PDO::PARAM_INT)
            ->setParameter(':versionNo', $versionNo, PDO::PARAM_INT)
        ;

        $imageXMLs = $selectQuery->execute()->fetchAll(FetchMode::COLUMN);
        foreach ($imageXMLs as $imageXML) {
            $storedFilePath = $this->extractFilesFromXml($imageXML)['original'] ?? null;
            if ($storedFilePath === $path) {
                return false;
            }
        }

        return true;
    }

    /**
     * Extract, stored in DocBook XML, file paths.
     *
     * @param string $xml
     *
     * @return array|null
     */
    public function extractFilesFromXml($xml)
    {
        if (empty($xml)) {
            // Empty image value
            return null;
        }

        $files = [];

        $dom = new DOMDocument();
        $dom->loadXml($xml);
        if ($dom->documentElement->hasAttribute('dirpath')) {
            $url = $dom->documentElement->getAttribute('url');
            if (empty($url)) {
                return null;
            }

            $files['original'] = $this->redecorator->redecorateFromTarget($url);
            foreach ($dom->documentElement->childNodes as $childNode) {
                /** @var \DOMElement $childNode */
                if ($childNode->nodeName !== 'alias') {
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

    public function getImagesData(int $offset, int $limit): array
    {
        $selectQuery = $this->connection->createQueryBuilder();
        $selectQuery
            ->select(
                'img.contentobject_attribute_id',
                'img.filepath'
            )
            ->distinct()
            ->from($this->connection->quoteIdentifier(self::IMAGE_FILE_TABLE), 'img')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $selectQuery->execute()->fetchAllAssociative();
    }

    public function updateImageData(int $fieldId, int $versionNo, string $xml): void
    {
        $updateQuery = $this->connection->createQueryBuilder();
        $expressionBuilder = $updateQuery->expr();
        $updateQuery
            ->update(
                $this->connection->quoteIdentifier('ezcontentobject_attribute')
            )
            ->set(
                $this->connection->quoteIdentifier('data_text'),
                ':xml'
            )
            ->where(
                $expressionBuilder->eq(
                    $this->connection->quoteIdentifier('id'),
                    ':field_id'
                )
            )
            ->andWhere(
                $expressionBuilder->eq(
                    $this->connection->quoteIdentifier('version'),
                    ':version_no'
                )
            )
            ->setParameter(':field_id', $fieldId, ParameterType::INTEGER)
            ->setParameter(':version_no', $versionNo, ParameterType::INTEGER)
            ->setParameter(':xml', $xml, ParameterType::STRING)
            ->execute()
        ;
    }

    public function updateImagePath(int $fieldId, string $oldPath, string $newPath): void
    {
        $updateQuery = $this->connection->createQueryBuilder();
        $expressionBuilder = $updateQuery->expr();
        $updateQuery
            ->update(
                $this->connection->quoteIdentifier(self::IMAGE_FILE_TABLE)
            )
            ->set(
                $this->connection->quoteIdentifier('filepath'),
                ':new_path'
            )
            ->where(
                $expressionBuilder->eq(
                    $this->connection->quoteIdentifier('contentobject_attribute_id'),
                    ':field_id'
                )
            )
            ->andWhere(
                $expressionBuilder->eq(
                    $this->connection->quoteIdentifier('filepath'),
                    ':old_path'
                )
            )
            ->setParameter(':field_id', $fieldId, ParameterType::INTEGER)
            ->setParameter(':old_path', $oldPath, ParameterType::STRING)
            ->setParameter(':new_path', $newPath, ParameterType::STRING)
            ->execute()
        ;
    }
}
