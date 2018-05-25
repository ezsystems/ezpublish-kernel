<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Command;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\Core\FieldType\Image\Value;
use eZ\Publish\Core\IO\IOServiceInterface;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
class RecreateImagesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('ezplatform:recreate-images')->setDefinition(
            [
                new InputArgument('contentTypeIdentifier', InputArgument::REQUIRED, 'Content Type identifier'),
                new InputArgument('imageField', InputArgument::REQUIRED, 'Image field name'),
                new InputArgument('variation', InputArgument::REQUIRED, 'Variation name'),
            ]
        );
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $contentTypeIdentifier = $input->getArgument('contentTypeIdentifier');
        $imageField = $input->getArgument('imageField');
        $variation = $input->getArgument('variation');
        $repository = $this->getContainer()->get('ezpublish.api.repository');
        $contentTypeService = $repository->getContentTypeService();
        $searchService = $repository->getSearchService();
        $filterManager = $this->getContainer()->get('liip_imagine.filter.manager');
        $ioService = $this->getContainer()->get('ezpublish.fieldType.ezimage.io_service.published');
        $extensionGuesser = $this->getContainer()->get('liip_imagine.extension_guesser');

        $contentType = $contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);
        $total = $repository->sudo(function() use($searchService, $contentType) {
            $query = new Query();
            $query->filter = new Query\Criterion\ContentTypeIdentifier($contentType->identifier);
            $query->limit = 0;
            return $searchService->findContent($query)->totalCount;
        });
        $offset = 0;
        $limit = 100;
        while( $offset < $total)
        {
            unset(
                $GLOBALS[ 'eZContentObjectContentObjectCache' ],
                $GLOBALS[ 'eZContentObjectDataMapCache' ],
                $GLOBALS[ 'eZContentObjectVersionCache' ]
            );
            $results = $repository->sudo(function() use($searchService, $contentType, $offset, $limit) {
                $query = new Query();
                $query->filter = new Query\Criterion\ContentTypeIdentifier($contentType->identifier);
                $query->limit = $limit;
                $query->offset = $offset;
                return $searchService->findContent($query);
            });
            $offset += $limit;
            /** @var SearchHit $hit */
            foreach ($results->searchHits as $hit) {
                /** @var Value $field */
                foreach ($hit->valueObject->fields[$imageField] as $language => $field) {
                    try {
                        // Can't use eZ\Bundle\EzPublishCoreBundle\Imagine\IORepositoryResolver::resolve() here
                        // because it always returns path prefixed by http://localhost when used via CLI.
                        // Most likely this solution won't work with DFS.
                        // Moreover, IORepositoryResolver always checks if given variation is *not* original
                        $binaryFile = $ioService->loadBinaryFile($field->id);
                        $mimeType = $ioService->getMimeType($field->id);
                        $binary = new Binary(
                            $ioService->getFileContents($binaryFile),
                            $mimeType,
                            $extensionGuesser->guess($mimeType)
                        );
                        $recreated = $filterManager->applyFilter($binary, $variation);
                        $this->store($ioService, $recreated, $field);
                        $output->writeln(sprintf('<info>Image ID: %s successfully recreated (%s)!</info>', $field->imageId, $field->id));
                    } catch (\Exception $exception) {
                        $output->writeln(sprintf('<error>Can not recreate image ID: %s, error message: %s</error>', $field->imageId, $exception->getMessage()));
                    }
                }
            }
        }
        
    }
    /**
     * Copy of eZ\Bundle\EzPublishCoreBundle\Imagine\IORepositoryResolver::store()
     * We can't use original one because original method uses eZ\Bundle\EzPublishCoreBundle\Imagine\IORepositoryResolver::getFilePath()
     * so we would end-up with image stored in _aliases instead of overwritten original image
     *
     * @param IOServiceInterface $IOService
     * @param BinaryInterface $binary
     * @param Value $image
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     */
    private function store(IOServiceInterface $IOService, BinaryInterface $binary, Value $image)
    {
        $tmpFile = tmpfile();
        fwrite($tmpFile, $binary->getContent());
        $tmpMetadata = stream_get_meta_data($tmpFile);
        $binaryCreateStruct = $IOService->newBinaryCreateStructFromLocalFile($tmpMetadata['uri']);
        $binaryCreateStruct->id = $image->id;
        $IOService->createBinaryFile($binaryCreateStruct);
        fclose($tmpFile);
    }
}