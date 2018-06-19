<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Command;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\Core\FieldType\Image\Value;
use eZ\Publish\Core\IO\IOServiceInterface;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Exception\Imagine\Filter\NonExistingFilterException;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResizeOriginalImagesCommand extends ContainerAwareCommand
{
    const DEFAULT_ITERATION_COUNT = 25;

    protected function configure()
    {
        $this->setName('ezplatform:resize-original-images')->setDefinition(
            [
                new InputArgument('contentTypeIdentifier', InputArgument::REQUIRED, 'Indentifier of ContentType which has ezimage FieldType.'),
                new InputArgument('imageFieldIdentifier', InputArgument::REQUIRED, 'Identifier of field of ezimage type.'),
                new InputArgument('variation', InputArgument::OPTIONAL, 'Variation which will be used for original images.', 'original'),
                new InputArgument('iteration-count', InputArgument::OPTIONAL, 'Iteration count. Number of images to be recreated in a single iteration, for avoiding using too much memory.',
                    self::DEFAULT_ITERATION_COUNT),
            ]
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $contentTypeIdentifier = $input->getArgument('contentTypeIdentifier');
        $imageField = $input->getArgument('imageFieldIdentifier');
        $variation = $input->getArgument('variation');
        $iterationCount = (int)$input->getArgument('iteration-count');

        $repository = $this->getContainer()->get('ezpublish.api.repository');
        $contentTypeService = $repository->getContentTypeService();
        $searchService = $repository->getSearchService();
        $filterManager = $this->getContainer()->get('liip_imagine.filter.manager');
        $ioService = $this->getContainer()->get('ezpublish.fieldType.ezimage.io_service.published');
        $extensionGuesser = $this->getContainer()->get('liip_imagine.extension_guesser');

        $contentType = $contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);
        $query = new Query();
        $query->filter = new Query\Criterion\ContentTypeIdentifier($contentType->identifier);

        $totalCount = $repository->sudo(function () use ($query, $searchService) {
            return $searchService->findContent($query)->totalCount;
        });

        $output->writeln(sprintf('Found %d images matching given criteria.', $totalCount));

        $progressBar = new ProgressBar($output, $totalCount);
        $progressBar->start();

        while ($query->offset <= $totalCount) {
            $query->limit = $iterationCount;
            $results = $repository->sudo(function () use ($query, $searchService) {
                return $searchService->findContent($query);
            });
            $query->offset += $iterationCount;

            /** @var SearchHit $hit */
            foreach ($results->searchHits as $hit) {
                try {
                    /** @var Value $field */
                    if (empty($hit->valueObject->fields[$imageField])) {
                        $output->writeln(sprintf("<error>ContentType '%s' does not have '%s' field</error>",
                            $contentType->identifier, $imageField));

                        return;
                    }
                    foreach ($hit->valueObject->fields[$imageField] as $language => $field) {
                        $binaryFile = $ioService->loadBinaryFile($field->id);
                        $mimeType = $ioService->getMimeType($field->id);
                        $binary = new Binary(
                            $ioService->getFileContents($binaryFile),
                            $mimeType,
                            $extensionGuesser->guess($mimeType)
                        );

                        $recreated = $filterManager->applyFilter($binary, $variation);
                        $this->store($ioService, $recreated, $field);
                    }
                } catch (NonExistingFilterException $e) {
                    $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

                    return;
                } catch (\Exception $e) {
                    $output->writeln(sprintf('<error>Can not resized image ID: %s, error message: %s</error>',
                        $field->imageId, $e->getMessage()));
                }

                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $output->writeln('');
        $output->writeln(sprintf("<info>All images has been successfully resized using '%s' variation.</info>",
            $variation));
    }

    /**
     * Copy of eZ\Bundle\EzPublishCoreBundle\Imagine\IORepositoryResolver::store()
     * We can't use original one because original method uses eZ\Bundle\EzPublishCoreBundle\Imagine\IORepositoryResolver::getFilePath()
     * so we would end-up with image stored in _aliases instead of overwritten original image.
     *
     * @param IOServiceInterface $IOService
     * @param BinaryInterface $binary
     * @param Value $image
     *
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
