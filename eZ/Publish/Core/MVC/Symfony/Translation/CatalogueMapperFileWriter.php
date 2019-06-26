<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Translation;

use JMS\TranslationBundle\Exception\InvalidArgumentException;
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
    /** @var LoaderManager */
    private $loaderManager;

    /** @var FileWriter */
    private $innerFileWriter;

    public function __construct(FileWriter $innerFileWriter, LoaderManager $loaderManager)
    {
        $this->loaderManager = $loaderManager;
        $this->innerFileWriter = $innerFileWriter;
    }

    public function write(MessageCatalogue $catalogue, $domain, $filePath, $format)
    {
        $newCatalogue = new MessageCatalogue();
        $newCatalogue->setLocale($catalogue->getLocale());

        foreach (array_keys($catalogue->getDomains()) as $catalogueDomainString) {
            if ($catalogue->getLocale() !== 'en' && $this->hasEnglishCatalogue($filePath)) {
                $englishCatalogue = $this->loadEnglishCatalogue($filePath, $domain, $format);
            }

            $domainMessageCollection = $catalogue->getDomain($catalogueDomainString);
            /** @var Message $message */
            foreach ($domainMessageCollection->all() as $message) {
                if ($message->getDomain() !== $domain) {
                    continue;
                }

                $newMessage = $this->makeXliffMessage($message);

                if ($message->getId() === $message->getSourceString()) {
                    if (isset($englishCatalogue)) {
                        try {
                            $newMessage->setDesc(
                                $englishCatalogue
                                    ->get($message->getId(), $message->getDomain())
                                    ->getLocaleString()
                            );
                        } catch (InvalidArgumentException $e) {
                            continue;
                        }
                    } else {
                        $newMessage->setDesc($message->getLocaleString());
                    }
                }

                $newCatalogue->add($newMessage);
            }
        }

        $this->innerFileWriter->write($newCatalogue, $domain, $filePath, $format);
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

    /**
     * @param $message
     * @return Message\XliffMessage
     */
    private function makeXliffMessage(Message $message)
    {
        $newMessage = new Message\XliffMessage($message->getId(), $message->getDomain());
        $newMessage->setNew($message->isNew());
        $newMessage->setLocaleString($message->getLocaleString());
        $newMessage->setSources($message->getSources());
        $newMessage->addNote('key: ' . $message->getId());

        if ($desc = $message->getDesc()) {
            $newMessage->setDesc($desc);
        }

        return $newMessage;
    }
}
