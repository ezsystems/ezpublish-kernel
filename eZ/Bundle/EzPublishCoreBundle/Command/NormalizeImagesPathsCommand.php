<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Command;

use eZ\Publish\Core\FieldType\Image\ImageStorage\Gateway\DoctrineStorage;
use eZ\Publish\Core\IO\FilePathNormalizerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class NormalizeImagesPathsCommand extends Command
{
    const BEFORE_RUNNING_HINTS = <<<EOT
<error>Before you continue:</error>
- Make sure to back up your database.
- Run this command in production environment using <info>--env=prod</info>
- Manually clear SPI/HTTP cache after running this command.
EOT;

    /** @var \eZ\Publish\Core\FieldType\Image\ImageStorage\Gateway\DoctrineStorage */
    private $imageGateway;

    /** @var \eZ\Publish\Core\IO\FilePathNormalizerInterface */
    private $filePathNormalizer;

    public function __construct(
        DoctrineStorage $imageGateway,
        FilePathNormalizerInterface $filePathNormalizer
    ) {
        parent::__construct();

        $this->imageGateway = $imageGateway;
        $this->filePathNormalizer = $filePathNormalizer;
    }

    protected function configure()
    {
        $this
            ->setName('ezplatform:normalize-image-paths')
            ->setDescription('Normalizes stored paths for images.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Normalize images paths');

        $io->writeln([
            'Determining the number of images that require path normalization.',
            'It may take a minute or two...',
        ]);

        $imagesData = $this->imageGateway->getAllImagesData();
        $imagePathsToNormalize = [];
        foreach ($imagesData as $imageData) {
            $filePath = $imageData['filepath'];
            $normalizedImagePath = $this->filePathNormalizer->normalizePath($imageData['filepath']);
            if ($normalizedImagePath !== $filePath) {
                $imagePathsToNormalize[] = [
                    'fieldId' => (int) $imageData['contentobject_attribute_id'],
                    'oldPath' => $filePath,
                    'newPath' => $normalizedImagePath,
                ];
            }
        }

        $imagePathsToNormalizeCount = \count($imagePathsToNormalize);
        $io->note(sprintf('Found: %d', $imagePathsToNormalizeCount));
        if ($imagePathsToNormalizeCount === 0) {
            $io->success('Nothing to do. Bye!');

            return 0;
        }

        if (!$io->confirm('Do you want to continue?')) {
            return 0;
        }

        $io->writeln('Normalizing images paths. Please wait...');
        $io->progressStart($imagePathsToNormalizeCount);
        foreach ($imagePathsToNormalize as $imagePathToNormalize) {
            $this->updateImagePath(
                $imagePathToNormalize['fieldId'],
                $imagePathToNormalize['oldPath'],
                $imagePathToNormalize['newPath']
            );
            $io->progressAdvance(1);
        }
        $io->progressFinish();
        $io->success('Done!');

        return 0;
    }

    private function updateImagePath(int $fieldId, string $oldPath, string $newPath): void
    {
        $oldPathInfo = pathinfo($oldPath);
        $newPathInfo = pathinfo($newPath);
        // In Image's XML, basename does not contain a file extension, and the filename does - pathinfo results are exactly the opposite.
        $oldFileName = $oldPathInfo['basename'];
        $newFilename = $newPathInfo['basename'];
        $newBaseName = $newPathInfo['filename'];

        $xmlsData = $this->imageGateway->getAllVersionsImageXmlForFieldId($fieldId);
        foreach ($xmlsData as $xmlData) {
            $dom = new \DOMDocument();
            $dom->loadXml($xmlData['data_text']);

            $ezimageTag = $dom->getElementsByTagName('ezimage')->item(0);
            $this->imageGateway->updateImagePath($fieldId, $oldPath, $newPath);
            if ($ezimageTag && $ezimageTag->getAttribute('filename') === $oldFileName) {
                $ezimageTag->setAttribute('filename', $newFilename);
                $ezimageTag->setAttribute('basename', $newBaseName);
                $ezimageTag->setAttribute('dirpath', $newPath);
                $ezimageTag->setAttribute('url', $newPath);

                $this->imageGateway->updateImageData($fieldId, $xmlData['version'], $dom->saveXML());
                $this->imageGateway->updateImagePath($fieldId, $oldPath, $newPath);
            }
        }
    }
}
