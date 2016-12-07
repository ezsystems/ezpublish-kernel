<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Migration\FileLister;

use eZ\Bundle\EzPublishIOBundle\ApiLoader\HandlerFactory;
use eZ\Bundle\EzPublishIOBundle\Migration\FileListerInterface;
use eZ\Bundle\EzPublishIOBundle\Migration\MigrationHandler;
use eZ\Publish\Core\IO\Exception\BinaryFileNotFoundException;
use Iterator;
use LimitIterator;
use Psr\Log\LoggerInterface;

final class BinaryFileLister extends MigrationHandler implements FileListerInterface
{
    /** @var \eZ\Bundle\EzPublishIOBundle\Migration\FileLister\FileIteratorInterface */
    private $fileList;

    /** @var string Directory where files are stored, within the storage dir. Example: 'original' */
    private $filesDir;

    /**
     * @param \eZ\Bundle\EzPublishIOBundle\ApiLoader\HandlerFactory $metadataHandlerFactory
     * @param \eZ\Bundle\EzPublishIOBundle\ApiLoader\HandlerFactory $binarydataHandlerFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Iterator $fileList
     * @param string $filesDir Directory where files are stored, within the storage dir. Example: 'original'
     */
    public function __construct(
        HandlerFactory $metadataHandlerFactory,
        HandlerFactory $binarydataHandlerFactory,
        LoggerInterface $logger = null,
        Iterator $fileList,
        $filesDir
    ) {
        $this->fileList = $fileList;
        $this->filesDir = $filesDir;

        $this->fileList->rewind();

        parent::__construct($metadataHandlerFactory, $binarydataHandlerFactory, $logger);
    }

    public function countFiles()
    {
        return count($this->fileList);
    }

    public function loadMetadataList($limit = null, $offset = null)
    {
        $metadataList = [];
        $fileLimitList = new LimitIterator($this->fileList, $offset, $limit);

        foreach ($fileLimitList as $fileId) {
            try {
                $metadataList[] = $this->fromMetadataHandler->load($this->filesDir . '/' . $fileId);
            } catch (BinaryFileNotFoundException $e) {
                $this->logMissingFile($fileId);

                continue;
            }
        }

        return $metadataList;
    }
}
