<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Migration\FileLister;

use eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPathGenerator;
use eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPurger\ImageFileList;
use eZ\Bundle\EzPublishIOBundle\ApiLoader\HandlerFactory;
use eZ\Bundle\EzPublishIOBundle\Migration\FileListerInterface;
use eZ\Bundle\EzPublishIOBundle\Migration\MigrationHandler;
use eZ\Publish\Core\IO\Exception\BinaryFileNotFoundException;
use Iterator;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use LimitIterator;
use Psr\Log\LoggerInterface;

final class ImageFileLister extends MigrationHandler implements FileListerInterface
{
    /** @var ImageFileList */
    private $imageFileList;

    /** @var \eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPathGenerator */
    private $variationPathGenerator;

    /** @var \Liip\ImagineBundle\Imagine\Filter\FilterConfiguration */
    private $filterConfiguration;

    /** @var string Directory where images are stored, within the storage dir. Example: 'images' */
    private $imagesDir;

    /**
     * @param \eZ\Bundle\EzPublishIOBundle\ApiLoader\HandlerFactory $metadataHandlerFactory
     * @param \eZ\Bundle\EzPublishIOBundle\ApiLoader\HandlerFactory $binarydataHandlerFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Iterator $imageFileList
     * @param \eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPathGenerator
     * @param \Liip\ImagineBundle\Imagine\Filter\FilterConfiguration
     * @param string $imagesDir Directory where images are stored, within the storage dir. Example: 'images'
     */
    public function __construct(
        HandlerFactory $metadataHandlerFactory,
        HandlerFactory $binarydataHandlerFactory,
        LoggerInterface $logger = null,
        Iterator $imageFileList,
        VariationPathGenerator $variationPathGenerator,
        FilterConfiguration $filterConfiguration,
        $imagesDir
    ) {
        $this->imageFileList = $imageFileList;
        $this->variationPathGenerator = $variationPathGenerator;
        $this->filterConfiguration = $filterConfiguration;
        $this->imagesDir = $imagesDir;

        $this->imageFileList->rewind();

        parent::__construct($metadataHandlerFactory, $binarydataHandlerFactory, $logger);
    }

    public function countFiles()
    {
        return count($this->imageFileList);
    }

    public function loadMetadataList($limit = null, $offset = null)
    {
        $metadataList = [];
        $imageLimitList = new LimitIterator($this->imageFileList, $offset, $limit);
        $aliasNames = array_keys($this->filterConfiguration->all());

        foreach ($imageLimitList as $originalImageId) {
            try {
                $metadataList[] = $this->fromMetadataHandler->load($this->imagesDir . '/' . $originalImageId);
            } catch (BinaryFileNotFoundException $e) {
                $this->logMissingFile($originalImageId);

                continue;
            }

            foreach ($aliasNames as $aliasName) {
                $variationImageId = $this->variationPathGenerator->getVariationPath($originalImageId, $aliasName);

                try {
                    $metadataList[] = $this->fromMetadataHandler->load($this->imagesDir . '/' . $variationImageId);
                } catch (BinaryFileNotFoundException $e) {
                    $this->logMissingFile($variationImageId);
                }
            }
        }

        return $metadataList;
    }
}
