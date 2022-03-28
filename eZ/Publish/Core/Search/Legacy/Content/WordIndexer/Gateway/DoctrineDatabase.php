<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\WordIndexer\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator;
use eZ\Publish\Core\Search\Legacy\Content\WordIndexer\Gateway;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\TransformationProcessor;
use eZ\Publish\Core\Search\Legacy\Content\WordIndexer\Repository\SearchIndex;
use eZ\Publish\Core\Search\Legacy\Content\FullTextData;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as SPITypeHandler;
use eZ\Publish\SPI\Search\Field;

/**
 * WordIndexer gateway implementation using the Doctrine database.
 */
class DoctrineDatabase extends Gateway
{
    /**
     * Max acceptable by any DBMS INT value.
     *
     * Note: 2^31-1 seems to be the most reasonable value that should work in any setup.
     */
    const DB_INT_MAX = 2147483647;

    /**
     * Database handler.
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     *
     * @deprecated Start to use DBAL $connection instead.
     */
    protected $dbHandler;

    /**
     * SPI Content Type Handler.
     *
     * Need this for being able to pick fields that are searchable.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    protected $typeHandler;

    /**
     * Transformation processor.
     *
     * Need this for being able to transform text to searchable value
     *
     * @var \eZ\Publish\Core\Persistence\TransformationProcessor
     */
    protected $transformationProcessor;

    /**
     * LegacySearchService.
     *
     * Need this for queries on ezsearch* tables
     *
     * @var \eZ\Publish\Core\Search\Legacy\Content\WordIndexer\Repository\SearchIndex
     */
    protected $searchIndex;

    /** @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator */
    private $languageMaskGenerator;

    /**
     * Full text search configuration options.
     *
     * @var array
     */
    protected $fullTextSearchConfiguration;

    /**
     * Construct from handler handler.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $typeHandler
     * @param \eZ\Publish\Core\Persistence\TransformationProcessor $transformationProcessor
     * @param \eZ\Publish\Core\Search\Legacy\Content\WordIndexer\Repository\SearchIndex $searchIndex
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator $languageMaskGenerator
     * @param array $fullTextSearchConfiguration
     */
    public function __construct(
        DatabaseHandler $dbHandler,
        SPITypeHandler $typeHandler,
        TransformationProcessor $transformationProcessor,
        SearchIndex $searchIndex,
        MaskGenerator $languageMaskGenerator,
        array $fullTextSearchConfiguration
    ) {
        $this->dbHandler = $dbHandler;
        $this->typeHandler = $typeHandler;
        $this->transformationProcessor = $transformationProcessor;
        $this->searchIndex = $searchIndex;
        $this->fullTextSearchConfiguration = $fullTextSearchConfiguration;
        $this->languageMaskGenerator = $languageMaskGenerator;
    }

    /**
     * Index search engine full text data corresponding to content object field values.
     *
     * Ported from the legacy code
     *
     * @see https://github.com/ezsystems/ezpublish-legacy/blob/master/kernel/search/plugins/ezsearchengine/ezsearchengine.php#L45
     *
     * @param \eZ\Publish\Core\Search\Legacy\Content\FullTextData $fullTextData
     */
    public function index(FullTextData $fullTextData)
    {
        $indexArray = [];
        $indexArrayOnlyWords = [];
        $wordCount = 0;
        $placement = 0;

        // Remove previously indexed content if exists to avoid keeping in index removed field values
        $this->remove($fullTextData->id);
        foreach ($fullTextData->values as $fullTextValue) {
            /** @var \eZ\Publish\Core\Search\Legacy\Content\FullTextValue $fullTextValue */
            if (is_numeric(trim($fullTextValue->value))) {
                $integerValue = (int)$fullTextValue->value;
                if ($integerValue > self::DB_INT_MAX) {
                    $integerValue = 0;
                }
            } else {
                $integerValue = 0;
            }
            $text = $this->transformationProcessor->transform(
                $fullTextValue->value,
                !empty($fullTextValue->transformationRules)
                    ? $fullTextValue->transformationRules
                    : $this->fullTextSearchConfiguration['commands']
            );

            // split by non-words
            $wordArray = $fullTextValue->splitFlag ? preg_split('/\W/u', $text, -1, PREG_SPLIT_NO_EMPTY) : [$text];
            foreach ($wordArray as $word) {
                if (trim($word) === '') {
                    continue;
                }
                // words stored in search index are limited to 150 characters
                if (mb_strlen($word) > 150) {
                    $word = mb_substr($word, 0, 150);
                }
                $indexArray[] = [
                    'Word' => $word,
                    'ContentClassAttributeID' => $fullTextValue->fieldDefinitionId,
                    'identifier' => $fullTextValue->fieldDefinitionIdentifier,
                    'integer_value' => $integerValue,
                    'language_code' => $fullTextValue->languageCode,
                    'is_main_and_always_available' => $fullTextValue->isMainAndAlwaysAvailable,
                ];
                $indexArrayOnlyWords[$word] = 1;
                ++$wordCount;
                // if we have "www." before word than
                // treat it as url and add additional entry to the index
                if (mb_strtolower(mb_substr($word, 0, 4)) === 'www.') {
                    $additionalUrlWord = substr($word, 4);
                    $indexArray[] = [
                        'Word' => $additionalUrlWord,
                        'ContentClassAttributeID' => $fullTextValue->fieldDefinitionId,
                        'identifier' => $fullTextValue->fieldDefinitionIdentifier,
                        'integer_value' => $integerValue,
                        'language_code' => $fullTextValue->languageCode,
                        'is_main_and_always_available' => $fullTextValue->isMainAndAlwaysAvailable,
                    ];
                    $indexArrayOnlyWords[$additionalUrlWord] = 1;
                    ++$wordCount;
                }
            }
        }

        $wordIDArray = $this->buildWordIDArray(array_keys($indexArrayOnlyWords));
        $this->dbHandler->beginTransaction();
        for ($arrayCount = 0; $arrayCount < $wordCount; $arrayCount += 1000) {
            $placement = $this->indexWords(
                $fullTextData,
                array_slice($indexArray, $arrayCount, 1000),
                $wordIDArray,
                $placement
            );
        }
        $this->dbHandler->commit();
    }

    /**
     * Indexes an array of FullTextData objects.
     *
     * Note: on large amounts of data make sure to iterate with several calls to this function with
     * a limited set of FullTextData objects. Amount you have memory for depends on server, size
     * of FullTextData objects & PHP version.
     *
     * @param \eZ\Publish\Core\Search\Legacy\Content\FullTextData[] $fullTextBulkData
     */
    public function bulkIndex(array $fullTextBulkData)
    {
        foreach ($fullTextBulkData as $fullTextData) {
            $this->index($fullTextData);
        }
    }

    /**
     * Remove whole content or a specific version from index.
     *
     * Ported from the legacy code
     *
     * @see https://github.com/ezsystems/ezpublish-legacy/blob/master/kernel/search/plugins/ezsearchengine/ezsearchengine.php#L386
     *
     * @param mixed $contentId
     * @param mixed|null $versionId
     *
     * @return bool
     */
    public function remove($contentId, $versionId = null)
    {
        $doDelete = false;
        $this->dbHandler->beginTransaction();
        // fetch all the words and decrease the object count on all the words
        $wordIDList = $this->searchIndex->getContentObjectWords($contentId);
        if (count($wordIDList) > 0) {
            $this->searchIndex->decrementWordObjectCount($wordIDList);
            $doDelete = true;
        }
        if ($doDelete) {
            $this->searchIndex->deleteWordsWithoutObjects();
            $this->searchIndex->deleteObjectWordsLink($contentId);
        }
        $this->dbHandler->commit();

        return true;
    }

    /**
     * Remove entire search index.
     */
    public function purgeIndex()
    {
        $this->searchIndex->purge();
    }

    /**
     * Index wordIndex.
     *
     * Ported from the legacy code
     *
     * @see https://github.com/ezsystems/ezpublish-legacy/blob/master/kernel/search/plugins/ezsearchengine/ezsearchengine.php#L255
     *
     * @param \eZ\Publish\Core\Search\Legacy\Content\FullTextData $fullTextData
     * @param array $indexArray
     * @param array $wordIDArray
     * @param int $placement
     *
     * @return int last placement
     */
    private function indexWords(FullTextData $fullTextData, array $indexArray, array $wordIDArray, $placement = 0)
    {
        $contentId = $fullTextData->id;

        $prevWordId = 0;

        for ($i = 0; $i < count($indexArray); ++$i) {
            $indexWord = $indexArray[$i]['Word'];
            $indexWord = $this->transformationProcessor->transformByGroup($indexWord, 'lowercase');
            $contentFieldId = $indexArray[$i]['ContentClassAttributeID'];
            $identifier = $indexArray[$i]['identifier'];
            $integerValue = $indexArray[$i]['integer_value'];
            $languageCode = $indexArray[$i]['language_code'];
            $wordId = $wordIDArray[$indexWord];
            $isMainAndAlwaysAvailable = $indexArray[$i]['is_main_and_always_available'];
            $languageMask = $this->languageMaskGenerator->generateLanguageMaskFromLanguageCodes(
                [$languageCode],
                $isMainAndAlwaysAvailable
            );

            if (isset($indexArray[$i + 1])) {
                $nextIndexWord = $indexArray[$i + 1]['Word'];
                $nextIndexWord = $this->transformationProcessor->transformByGroup($nextIndexWord, 'lowercase');
                $nextWordId = $wordIDArray[$nextIndexWord];
            } else {
                $nextWordId = 0;
            }
            $frequency = 0;
            $this->searchIndex->addObjectWordLink(
                $wordId,
                $contentId,
                $frequency,
                $placement,
                $nextWordId,
                $prevWordId,
                $fullTextData->contentTypeId,
                $contentFieldId,
                $fullTextData->published,
                $fullTextData->sectionId,
                $identifier,
                $integerValue,
                $languageMask
            );
            $prevWordId = $wordId;
            ++$placement;
        }

        return $placement;
    }

    /**
     * Build WordIDArray and update ezsearch_word table.
     *
     * Ported from the legacy code
     *
     * @see https://github.com/ezsystems/ezpublish-legacy/blob/master/kernel/search/plugins/ezsearchengine/ezsearchengine.php#L155
     *
     * @param array $indexArrayOnlyWords words for object to add
     *
     * @return array wordIDArray
     */
    private function buildWordIDArray(array $indexArrayOnlyWords)
    {
        $wordCount = count($indexArrayOnlyWords);
        $wordIDArray = [];
        $wordArray = [];

        // store the words in the index and remember the ID
        $this->dbHandler->beginTransaction();
        for ($arrayCount = 0; $arrayCount < $wordCount; $arrayCount += 500) {
            // Fetch already indexed words from database
            $wordArrayChuck = array_slice($indexArrayOnlyWords, $arrayCount, 500);
            $wordRes = $this->searchIndex->getWords($wordArrayChuck);

            // Build a has of the existing words
            $wordResCount = count($wordRes);
            $existingWordArray = [];
            for ($i = 0; $i < $wordResCount; ++$i) {
                $wordIDArray[] = $wordRes[$i]['id'];
                $existingWordArray[] = $wordRes[$i]['word'];
                $wordArray[$wordRes[$i]['word']] = $wordRes[$i]['id'];
            }

            // Update the object count of existing words by one
            if (count($wordIDArray) > 0) {
                $this->searchIndex->incrementWordObjectCount($wordIDArray);
            }

            // Insert if there is any news words
            $newWordArray = array_diff($wordArrayChuck, $existingWordArray);
            if (count($newWordArray) > 0) {
                $this->searchIndex->addWords($newWordArray);
                $newWordRes = $this->searchIndex->getWords($newWordArray);
                $newWordCount = count($newWordRes);
                for ($i = 0; $i < $newWordCount; ++$i) {
                    $wordLowercase = $this->transformationProcessor->transformByGroup($newWordRes[$i]['word'], 'lowercase');
                    $wordArray[$wordLowercase] = $newWordRes[$i]['id'];
                }
            }
        }
        $this->dbHandler->commit();

        return $wordArray;
    }
}
