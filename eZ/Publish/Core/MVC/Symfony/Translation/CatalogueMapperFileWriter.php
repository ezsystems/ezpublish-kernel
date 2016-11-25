<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Translation;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\FileWriter;
use JMS\TranslationBundle\Translation\LoaderManager;

/**
 * Before writing a catalogue to a file, maps the given catalogue with the english sources:
 * if a message doesn't have a human readable source (e.g. an id), sets the source to the
 * matching english string.
 */
class CatalogueMapperFileWriter extends FileWriter
{
    /**
     * @var LoaderManager
     */
    private $loaderManager;

    /**
     * @var FileWriter
     */
    private $innerFileWriter;

    public function __construct(FileWriter $innerFileWriter, LoaderManager $loaderManager)
    {
        $this->loaderManager = $loaderManager;
        $this->innerFileWriter = $innerFileWriter;
    }

    public function write(MessageCatalogue $catalogue, $domain, $filePath, $format)
    {
        if ($catalogue->getLocale() !== 'en' && $this->hasEnglishCatalogue($filePath)) {
            $englishCatalogue = $this->loadEnglishCatalogue($filePath, $domain, $format);

            foreach (array_keys($catalogue->getDomains()) as $catalogueDomainString) {
                $domainMessageCollection = $catalogue->getDomain($catalogueDomainString);
                /** @var Message $message */
                foreach ($domainMessageCollection->all() as $message) {
                    if ($message->getDomain() !== $domain) {
                        continue;
                    }

                    if ($message->getId() !== $message->getSourceString()) {
                        continue;
                    }

                    try {
                        $englishString = $englishCatalogue
                            ->get($message->getId(), $message->getDomain())
                            ->getLocaleString();
                    } catch (\JMS\TranslationBundle\Exception\InvalidArgumentException $e) {
                        continue;
                    }
                    $message->setDesc($englishString);
                    $catalogue->set($message);
                }
            }
        } else {
            foreach (array_keys($catalogue->getDomains()) as $catalogueDomainString) {
                $domainMessageCollection = $catalogue->getDomain($catalogueDomainString);
                /** @var Message $message */
                foreach ($domainMessageCollection->all() as $message) {
                    if ($message->getDomain() !== $domain) {
                        continue;
                    }

                    if ($message->getId() !== $message->getSourceString()) {
                        continue;
                    }

                    $message->setDesc($message->getLocaleString());
                    $catalogue->set($message);
                }
            }
        }

        $this->innerFileWriter->write($catalogue, $domain, $filePath, $format);
    }

    /**
     * @param $filePath
     * @return mixed
     */
    private function getEnglishFilePath($filePath)
    {
        return preg_replace('/\.[-_a-z]+\.xlf$/i', '.en.xlf', $filePath);
    }

    /**
     * @param $foreignFilePath
     * @param $domain
     * @param $format
     * @return MessageCatalogue
     */
    private function loadEnglishCatalogue($foreignFilePath, $domain, $format)
    {
        return $this->loaderManager->loadFile(
            $this->getEnglishFilePath($foreignFilePath),
            $format,
            'en',
            $domain
        );
    }

    private function hasEnglishCatalogue($foreignFilePath)
    {
        return file_exists($this->getEnglishFilePath($foreignFilePath));
    }
}
