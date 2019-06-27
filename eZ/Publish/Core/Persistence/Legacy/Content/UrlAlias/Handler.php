<?php

/**
 * File containing the UrlAlias Handler.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias;

use eZ\Publish\Core\Base\Exceptions\BadStateException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\DTO\SwappedLocationProperties;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\DTO\UrlAliasForSwappedLocation;
use eZ\Publish\SPI\Persistence\Content\Language;
use eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler as UrlAliasHandlerInterface;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway as ContentGateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway as LocationGateway;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\ForbiddenException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use eZ\Publish\SPI\Persistence\TransactionHandler;

/**
 * The UrlAlias Handler provides nice urls management.
 *
 * Its methods operate on a representation of the url alias data structure held
 * inside a storage engine.
 */
class Handler implements UrlAliasHandlerInterface
{
    const ROOT_LOCATION_ID = 1;

    /**
     * This is intentionally hardcoded for now as:
     * 1. We don't implement this configuration option.
     * 2. Such option should not be in this layer, should be handled higher up.
     *
     * @deprecated
     */
    const CONTENT_REPOSITORY_ROOT_LOCATION_ID = 2;

    /**
     * The maximum level of alias depth.
     */
    const MAX_URL_ALIAS_DEPTH_LEVEL = 60;

    /**
     * UrlAlias Gateway.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway
     */
    protected $gateway;

    /**
     * Gateway for handling location data.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway
     */
    protected $locationGateway;

    /**
     * UrlAlias Mapper.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Mapper
     */
    protected $mapper;

    /**
     * Caching language handler.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler
     */
    protected $languageHandler;

    /**
     * URL slug converter.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter
     */
    protected $slugConverter;

    /**
     * Gateway for handling content data.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    protected $contentGateway;

    /**
     * Language mask generator.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected $maskGenerator;

    /** @var \eZ\Publish\SPI\Persistence\TransactionHandler */
    private $transactionHandler;

    /**
     * Creates a new UrlAlias Handler.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway $gateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Mapper $mapper
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway $locationGateway
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $languageHandler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter $slugConverter
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Gateway $contentGateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator $maskGenerator
     * @param \eZ\Publish\SPI\Persistence\TransactionHandler $transactionHandler
     */
    public function __construct(
        Gateway $gateway,
        Mapper $mapper,
        LocationGateway $locationGateway,
        LanguageHandler $languageHandler,
        SlugConverter $slugConverter,
        ContentGateway $contentGateway,
        MaskGenerator $maskGenerator,
        TransactionHandler $transactionHandler
    ) {
        $this->gateway = $gateway;
        $this->mapper = $mapper;
        $this->locationGateway = $locationGateway;
        $this->languageHandler = $languageHandler;
        $this->slugConverter = $slugConverter;
        $this->contentGateway = $contentGateway;
        $this->maskGenerator = $maskGenerator;
        $this->transactionHandler = $transactionHandler;
    }

    public function publishUrlAliasForLocation(
        $locationId,
        $parentLocationId,
        $name,
        $languageCode,
        $alwaysAvailable = false,
        $updatePathIdentificationString = false
    ) {
        $languageId = $this->languageHandler->loadByLanguageCode($languageCode)->id;

        $this->internalPublishUrlAliasForLocation(
            $locationId,
            $parentLocationId,
            $name,
            $languageId,
            $alwaysAvailable,
            $updatePathIdentificationString
        );
    }

    /**
     * Internal publish method, accepting language ID instead of language code and optionally
     * new alias ID (used when swapping Locations).
     *
     * @see \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::locationSwapped()
     *
     * @param int $locationId
     * @param int $parentLocationId
     * @param string $name
     * @param int $languageId
     * @param bool $alwaysAvailable
     * @param bool $updatePathIdentificationString legacy storage specific for updating ezcontentobject_tree.path_identification_string
     * @param int $newId
     */
    private function internalPublishUrlAliasForLocation(
        $locationId,
        $parentLocationId,
        $name,
        $languageId,
        $alwaysAvailable = false,
        $updatePathIdentificationString = false,
        $newId = null
    ) {
        $parentId = $this->getRealAliasId($parentLocationId);
        $name = $this->slugConverter->convert($name, 'location_' . $locationId);
        $uniqueCounter = $this->slugConverter->getUniqueCounterValue($name, $parentId == 0);
        $languageMask = $languageId | (int)$alwaysAvailable;
        $action = 'eznode:' . $locationId;
        $cleanup = false;

        // Exiting the loop with break;
        while (true) {
            $newText = '';
            if ($locationId != self::CONTENT_REPOSITORY_ROOT_LOCATION_ID) {
                $newText = $name . ($name == '' || $uniqueCounter > 1 ? $uniqueCounter : '');
            }
            $newTextMD5 = $this->getHash($newText);

            // Try to load existing entry
            $row = $this->gateway->loadRow($parentId, $newTextMD5);

            // If nothing was returned insert new entry
            if (empty($row)) {
                // Check for existing active location entry on this level and reuse it's id
                $existingLocationEntry = $this->gateway->loadAutogeneratedEntry($action, $parentId);
                if (!empty($existingLocationEntry)) {
                    $cleanup = true;
                    $newId = $existingLocationEntry['id'];
                }

                try {
                    $newId = $this->gateway->insertRow(
                        [
                            'id' => $newId,
                            'link' => $newId,
                            'parent' => $parentId,
                            'action' => $action,
                            'lang_mask' => $languageMask,
                            'text' => $newText,
                            'text_md5' => $newTextMD5,
                        ]
                    );
                } catch (\RuntimeException $e) {
                    while ($e->getPrevious() !== null) {
                        $e = $e->getPrevious();
                        if ($e instanceof UniqueConstraintViolationException) {
                            // Concurrency! someone else inserted the same row that we where going to.
                            // let's do another loop pass
                            $uniqueCounter += 1;
                            continue 2;
                        }
                    }

                    throw $e;
                }

                break;
            }

            // Row exists, check if it is reusable. There are 3 cases when this is possible:
            // 1. NOP entry
            // 2. existing location or custom alias entry
            // 3. history entry
            if ($row['action'] == 'nop:' || $row['action'] == $action || $row['is_original'] == 0) {
                // Check for existing location entry on this level, if it exists and it's id differs from reusable
                // entry id then reusable entry should be updated with the existing location entry id.
                // Note: existing location entry may be downgraded and relinked later, depending on its language.
                $existingLocationEntry = $this->gateway->loadAutogeneratedEntry($action, $parentId);

                if (!empty($existingLocationEntry)) {
                    // Always cleanup when active autogenerated entry exists on the same level
                    $cleanup = true;
                    $newId = $existingLocationEntry['id'];
                    if ($existingLocationEntry['id'] == $row['id']) {
                        // If we are reusing existing location entry merge existing language mask
                        $languageMask |= ($row['lang_mask'] & ~1);
                    }
                } elseif ($newId === null) {
                    // Use reused row ID only if publishing normally, else use given $newId
                    $newId = $row['id'];
                }

                $this->gateway->updateRow(
                    $parentId,
                    $newTextMD5,
                    [
                        'action' => $action,
                        // In case when NOP row was reused
                        'action_type' => 'eznode',
                        'lang_mask' => $languageMask,
                        // Updating text ensures that letter case changes are stored
                        'text' => $newText,
                        // Set "id" and "link" for case when reusable entry is history
                        'id' => $newId,
                        'link' => $newId,
                        // Entry should be active location entry (original and not alias).
                        // Note: this takes care of taking over custom alias entry for the location on the same level
                        // and with same name and action.
                        'alias_redirects' => 1,
                        'is_original' => 1,
                        'is_alias' => 0,
                    ]
                );

                break;
            }

            // If existing row is not reusable, increment $uniqueCounter and try again
            $uniqueCounter += 1;
        }

        /* @var $newText */
        if ($updatePathIdentificationString) {
            $this->locationGateway->updatePathIdentificationString(
                $locationId,
                $parentLocationId,
                $this->slugConverter->convert($newText, 'node_' . $locationId, 'urlalias_compat')
            );
        }

        /* @var $newId */
        /* @var $newTextMD5 */
        // Note: cleanup does not touch custom and global entries
        if ($cleanup) {
            $this->gateway->cleanupAfterPublish($action, $languageId, $newId, $parentId, $newTextMD5);
        }
    }

    /**
     * Create a user chosen $alias pointing to $locationId in $languageCode.
     *
     * If $languageCode is null the $alias is created in the system's default
     * language. $alwaysAvailable makes the alias available in all languages.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     *
     * @param mixed $locationId
     * @param string $path
     * @param bool $forwarding
     * @param string $languageCode
     * @param bool $alwaysAvailable
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     */
    public function createCustomUrlAlias($locationId, $path, $forwarding = false, $languageCode = null, $alwaysAvailable = false)
    {
        return $this->createUrlAlias(
            'eznode:' . $locationId,
            $path,
            $forwarding,
            $languageCode,
            $alwaysAvailable
        );
    }

    /**
     * Create a user chosen $alias pointing to a resource in $languageCode.
     * This method does not handle location resources - if a user enters a location target
     * the createCustomUrlAlias method has to be used.
     *
     * If $languageCode is null the $alias is created in the system's default
     * language. $alwaysAvailable makes the alias available in all languages.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException if the path already exists for the given language
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if the path is broken
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @param string $resource
     * @param string $path
     * @param bool $forwarding
     * @param string $languageCode
     * @param bool $alwaysAvailable
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     */
    public function createGlobalUrlAlias($resource, $path, $forwarding = false, $languageCode = null, $alwaysAvailable = false)
    {
        return $this->createUrlAlias(
            $resource,
            $path,
            $forwarding,
            $languageCode,
            $alwaysAvailable
        );
    }

    /**
     * Internal method for creating global or custom URL alias (these are handled in the same way).
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\ForbiddenException if the path already exists for the given language
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     *
     * @param string $action
     * @param string $path
     * @param bool $forward
     * @param string|null $languageCode
     * @param bool $alwaysAvailable
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     */
    protected function createUrlAlias($action, $path, $forward, $languageCode, $alwaysAvailable)
    {
        $pathElements = explode('/', $path);
        $topElement = array_pop($pathElements);
        $languageId = $this->languageHandler->loadByLanguageCode($languageCode)->id;
        $parentId = 0;

        // Handle all path elements except topmost one
        $isPathNew = false;
        foreach ($pathElements as $level => $pathElement) {
            $pathElement = $this->slugConverter->convert($pathElement, 'noname' . ($level + 1));
            $pathElementMD5 = $this->getHash($pathElement);
            if (!$isPathNew) {
                $row = $this->gateway->loadRow($parentId, $pathElementMD5);
                if (empty($row)) {
                    $isPathNew = true;
                } else {
                    $parentId = $row['link'];
                }
            }

            if ($isPathNew) {
                $parentId = $this->insertNopEntry($parentId, $pathElement, $pathElementMD5);
            }
        }

        // Handle topmost path element
        $topElement = $this->slugConverter->convert($topElement, 'noname' . (count($pathElements) + 1));

        // If last (next to topmost) entry parent is special root entry we handle topmost entry as first level entry
        // That is why we need to reset $parentId to 0
        if ($parentId != 0 && $this->gateway->isRootEntry($parentId)) {
            $parentId = 0;
        }

        $topElementMD5 = $this->getHash($topElement);
        // Set common values for two cases below
        $data = [
            'action' => $action,
            'is_alias' => 1,
            'alias_redirects' => $forward ? 1 : 0,
            'parent' => $parentId,
            'text' => $topElement,
            'text_md5' => $topElementMD5,
            'is_original' => 1,
        ];
        // Try to load topmost element
        if (!$isPathNew) {
            $row = $this->gateway->loadRow($parentId, $topElementMD5);
        }

        // If nothing was returned perform insert
        if ($isPathNew || empty($row)) {
            $data['lang_mask'] = $languageId | (int)$alwaysAvailable;
            $id = $this->gateway->insertRow($data);
        } elseif ($row['action'] == 'nop:' || $row['is_original'] == 0) {
            // Row exists, check if it is reusable. There are 2 cases when this is possible:
            // 1. NOP entry
            // 2. history entry
            $data['lang_mask'] = $languageId | (int)$alwaysAvailable;
            // If history is reused move link to id
            $data['link'] = $id = $row['id'];
            $this->gateway->updateRow(
                $parentId,
                $topElementMD5,
                $data
            );
        } else {
            throw new ForbiddenException("Path '%path%' already exists for the given language", ['%path%' => $path]);
        }

        $data['raw_path_data'] = $this->gateway->loadPathData($id);

        return $this->mapper->extractUrlAliasFromData($data);
    }

    /**
     * Convenience method for inserting nop type row.
     *
     * @param mixed $parentId
     * @param string $text
     * @param string $textMD5
     *
     * @return mixed
     */
    protected function insertNopEntry($parentId, $text, $textMD5)
    {
        return $this->gateway->insertRow(
            [
                'lang_mask' => 1,
                'action' => 'nop:',
                'parent' => $parentId,
                'text' => $text,
                'text_md5' => $textMD5,
            ]
        );
    }

    /**
     * List of user generated or autogenerated url entries, pointing to $locationId.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     *
     * @param mixed $locationId
     * @param bool $custom if true the user generated aliases are listed otherwise the autogenerated
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias[]
     */
    public function listURLAliasesForLocation($locationId, $custom = false)
    {
        $data = $this->gateway->loadLocationEntries($locationId, $custom);
        foreach ($data as &$entry) {
            $entry['raw_path_data'] = $this->gateway->loadPathData($entry['id']);
        }

        return $this->mapper->extractUrlAliasListFromData($data);
    }

    /**
     * List global aliases.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     *
     * @param string|null $languageCode
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias[]
     */
    public function listGlobalURLAliases($languageCode = null, $offset = 0, $limit = -1)
    {
        $data = $this->gateway->listGlobalEntries($languageCode, $offset, $limit);
        foreach ($data as &$entry) {
            $entry['raw_path_data'] = $this->gateway->loadPathData($entry['id']);
        }

        return $this->mapper->extractUrlAliasListFromData($data);
    }

    /**
     * Removes url aliases.
     *
     * Autogenerated aliases are not removed by this method.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\UrlAlias[] $urlAliases
     *
     * @return bool
     */
    public function removeURLAliases(array $urlAliases)
    {
        foreach ($urlAliases as $urlAlias) {
            if ($urlAlias->isCustom) {
                list($parentId, $textMD5) = explode('-', $urlAlias->id);
                if (!$this->gateway->removeCustomAlias($parentId, $textMD5)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Looks up a url alias for the given url.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     *
     * @param string $url
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     */
    public function lookup($url)
    {
        $urlHashes = [];
        foreach (explode('/', $url) as $level => $text) {
            $urlHashes[$level] = $this->getHash($text);
        }

        $pathDepth = count($urlHashes);
        if ($pathDepth > self::MAX_URL_ALIAS_DEPTH_LEVEL) {
            throw new InvalidArgumentException('$urlHashes', 'Exceeded maximum depth level of content url alias.');
        }

        $data = $this->gateway->loadUrlAliasData($urlHashes);
        if (empty($data)) {
            throw new NotFoundException('URLAlias', $url);
        }

        $hierarchyData = [];
        $isPathHistory = false;
        for ($level = 0; $level < $pathDepth; ++$level) {
            $prefix = $level === $pathDepth - 1 ? '' : 'ezurlalias_ml' . $level . '_';
            $isPathHistory = $isPathHistory ?: ($data[$prefix . 'link'] != $data[$prefix . 'id']);
            $hierarchyData[$level] = [
                'id' => $data[$prefix . 'id'],
                'parent' => $data[$prefix . 'parent'],
                'action' => $data[$prefix . 'action'],
            ];
        }

        $data['is_path_history'] = $isPathHistory;
        $data['raw_path_data'] = ($data['action_type'] == 'eznode' && !$data['is_alias'])
            ? $this->gateway->loadPathDataByHierarchy($hierarchyData)
            : $this->gateway->loadPathData($data['id']);

        return $this->mapper->extractUrlAliasFromData($data);
    }

    /**
     * Loads URL alias by given $id.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     *
     * @param string $id
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     */
    public function loadUrlAlias($id)
    {
        list($parentId, $textMD5) = explode('-', $id);
        $data = $this->gateway->loadRow($parentId, $textMD5);

        if (empty($data)) {
            throw new NotFoundException('URLAlias', $id);
        }

        $data['raw_path_data'] = $this->gateway->loadPathData($data['id']);

        return $this->mapper->extractUrlAliasFromData($data);
    }

    /**
     * Notifies the underlying engine that a location has moved.
     *
     * This method triggers the change of the autogenerated aliases.
     *
     * @param mixed $locationId
     * @param mixed $oldParentId
     * @param mixed $newParentId
     */
    public function locationMoved($locationId, $oldParentId, $newParentId)
    {
        // @todo optimize: $newLocationAliasId is already available in self::publishUrlAliasForLocation() as $newId
        $newParentLocationAliasId = $this->getRealAliasId($newParentId);
        $newLocationAlias = $this->gateway->loadAutogeneratedEntry(
            'eznode:' . $locationId,
            $newParentLocationAliasId
        );

        $oldParentLocationAliasId = $this->getRealAliasId($oldParentId);
        $oldLocationAlias = $this->gateway->loadAutogeneratedEntry(
            'eznode:' . $locationId,
            $oldParentLocationAliasId
        );

        // Historize alias for old location
        $this->gateway->historizeId($oldLocationAlias['id'], $newLocationAlias['id']);
        // Reparent subtree of old location to new location
        $this->gateway->reparent($oldLocationAlias['id'], $newLocationAlias['id']);
    }

    /**
     * Notifies the underlying engine that a location was copied.
     *
     * This method triggers the creation of the autogenerated aliases for the copied locations
     *
     * @param mixed $locationId
     * @param mixed $newLocationId
     * @param mixed $newParentId
     */
    public function locationCopied($locationId, $newLocationId, $newParentId)
    {
        $newParentAliasId = $this->getRealAliasId($newLocationId);
        $oldParentAliasId = $this->getRealAliasId($locationId);

        $actionMap = $this->getCopiedLocationsMap($locationId, $newLocationId);

        $this->copySubtree(
            $actionMap,
            $oldParentAliasId,
            $newParentAliasId
        );
    }

    /**
     * Notify the underlying engine that a Location has been swapped.
     *
     * This method triggers the change of the autogenerated aliases.
     *
     * @param int $location1Id
     * @param int $location1ParentId
     * @param int $location2Id
     * @param int $location2ParentId
     */
    public function locationSwapped($location1Id, $location1ParentId, $location2Id, $location2ParentId)
    {
        $location1 = new SwappedLocationProperties($location1Id, $location1ParentId);
        $location2 = new SwappedLocationProperties($location2Id, $location2ParentId);

        $location1->entries = $this->gateway->loadLocationEntries($location1Id);
        $location2->entries = $this->gateway->loadLocationEntries($location2Id);

        $location1->mainLanguageId = $this->gateway->getLocationContentMainLanguageId($location1Id);
        $location2->mainLanguageId = $this->gateway->getLocationContentMainLanguageId($location2Id);

        // Load autogenerated entries to find alias ID
        $location1->autogeneratedId = $this->gateway->loadAutogeneratedEntry("eznode:{$location1Id}")['id'];
        $location2->autogeneratedId = $this->gateway->loadAutogeneratedEntry("eznode:{$location2Id}")['id'];

        $contentInfo1 = $this->contentGateway->loadContentInfoByLocationId($location1Id);
        $contentInfo2 = $this->contentGateway->loadContentInfoByLocationId($location2Id);

        $names1 = $this->getNamesForAllLanguages($contentInfo1);
        $names2 = $this->getNamesForAllLanguages($contentInfo2);

        $location1->isAlwaysAvailable = $this->maskGenerator->isAlwaysAvailable($contentInfo1['language_mask']);
        $location2->isAlwaysAvailable = $this->maskGenerator->isAlwaysAvailable($contentInfo2['language_mask']);

        $languages = $this->languageHandler->loadAll();

        // Historize everything first to avoid name conflicts in case swapped Locations are siblings
        $this->historizeBeforeSwap($location1->entries, $location2->entries);

        foreach ($languages as $languageCode => $language) {
            $location1->name = isset($names1[$languageCode]) ? $names1[$languageCode] : null;
            $location2->name = isset($names2[$languageCode]) ? $names2[$languageCode] : null;
            $urlAliasesForSwappedLocations = $this->getUrlAliasesForSwappedLocations(
                $language,
                $location1,
                $location2
            );
            foreach ($urlAliasesForSwappedLocations as $urlAliasForLocation) {
                $this->internalPublishUrlAliasForLocation(
                    $urlAliasForLocation->id,
                    $urlAliasForLocation->parentId,
                    $urlAliasForLocation->name,
                    $language->id,
                    $urlAliasForLocation->isAlwaysAvailable,
                    $urlAliasForLocation->isPathIdentificationStringModified,
                    $urlAliasForLocation->newId
                );
            }
        }
    }

    /**
     * @param array $contentInfo
     *
     * @return array
     */
    private function getNamesForAllLanguages(array $contentInfo)
    {
        $nameDataArray = $this->contentGateway->loadVersionedNameData([
            [
                'id' => $contentInfo['id'],
                'version' => $contentInfo['current_version'],
            ],
        ]);

        $namesForAllLanguages = [];
        foreach ($nameDataArray as $nameData) {
            $namesForAllLanguages[$nameData['ezcontentobject_name_content_translation']]
                = $nameData['ezcontentobject_name_name'];
        }

        return $namesForAllLanguages;
    }

    /**
     * Historizes given existing active entries for two swapped Locations.
     *
     * This should be done before republishing URL aliases, in order to avoid unnecessary
     * conflicts when swapped Locations are siblings.
     *
     * We need to historize everything separately per language (mask), in case the entries
     * remain history future publishing reusages need to be able to take them over cleanly.
     *
     * @see \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::locationSwapped()
     *
     * @param array $location1Entries
     * @param array $location2Entries
     */
    private function historizeBeforeSwap($location1Entries, $location2Entries)
    {
        foreach ($location1Entries as $row) {
            $this->gateway->historizeBeforeSwap($row['action'], $row['lang_mask']);
        }

        foreach ($location2Entries as $row) {
            $this->gateway->historizeBeforeSwap($row['action'], $row['lang_mask']);
        }
    }

    /**
     * Decides if UrlAlias for $location2 should be published first.
     *
     * The order in which Locations are published only matters if swapped Locations are siblings and they have the same
     * name in a given language. In this case, the UrlAlias for Location which previously had lower number at the end of
     * its UrlAlias text (or no number at all) should be published first. This ensures that the number still stays lower
     * for this Location after the swap. If it wouldn't stay lower, then swapping Locations in conjunction with swapping
     * UrlAliases would effectively cancel each other.
     *
     * @param array $location1Entries
     * @param int $location1ParentId
     * @param string $name1
     * @param array $location2Entries
     * @param int $location2ParentId
     * @param string $name2
     * @param int $languageId
     *
     * @return bool
     */
    private function shouldUrlAliasForSecondLocationBePublishedFirst(
        array $location1Entries,
        $location1ParentId,
        $name1,
        array $location2Entries,
        $location2ParentId,
        $name2,
        $languageId
    ) {
        if ($location1ParentId === $location2ParentId && $name1 === $name2) {
            $locationEntry1 = $this->getLocationEntryInLanguage($location1Entries, $languageId);
            $locationEntry2 = $this->getLocationEntryInLanguage($location2Entries, $languageId);

            if ($locationEntry1 === null || $locationEntry2 === null) {
                return false;
            }

            if ($locationEntry2['text'] < $locationEntry1['text']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get in a proper order - to be published - a list of URL aliases for swapped Locations.
     *
     * @see shouldUrlAliasForSecondLocationBePublishedFirst
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language $language
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\DTO\SwappedLocationProperties $location1
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\DTO\SwappedLocationProperties $location2
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\DTO\UrlAliasForSwappedLocation[]
     */
    private function getUrlAliasesForSwappedLocations(
        Language $language,
        SwappedLocationProperties $location1,
        SwappedLocationProperties $location2
    ) {
        $isMainLanguage1 = $language->id == $location1->mainLanguageId;
        $isMainLanguage2 = $language->id == $location2->mainLanguageId;
        $urlAliases = [];
        if (isset($location1->name)) {
            $urlAliases[] = new UrlAliasForSwappedLocation(
                $location1->id,
                $location1->parentId,
                $location1->name,
                $isMainLanguage2 && $location1->isAlwaysAvailable,
                $isMainLanguage2,
                $location1->autogeneratedId
            );
        }

        if (isset($location2->name)) {
            $urlAliases[] = new UrlAliasForSwappedLocation(
                $location2->id,
                $location2->parentId,
                $location2->name,
                $isMainLanguage1 && $location2->isAlwaysAvailable,
                $isMainLanguage1,
                $location2->autogeneratedId
            );

            if (isset($location1->name) && $this->shouldUrlAliasForSecondLocationBePublishedFirst(
                    $location1->entries,
                    $location1->parentId,
                    $location1->name,
                    $location2->entries,
                    $location2->parentId,
                    $location2->name,
                    $language->id
                )) {
                $urlAliases = array_reverse($urlAliases);
            }
        }

        return $urlAliases;
    }

    /**
     * @param array $locationEntries
     * @param int $languageId
     *
     * @return array|null
     */
    private function getLocationEntryInLanguage(array $locationEntries, $languageId)
    {
        $entries = array_filter(
            $locationEntries,
            function (array $row) use ($languageId) {
                return (bool) ($row['lang_mask'] & $languageId);
            }
        );

        return !empty($entries) ? array_shift($entries) : null;
    }

    /**
     * Returns possibly corrected alias id for given $locationId !! For use as parent id in logic.
     *
     * First level entries must have parent id set to 0 instead of their parent location alias id.
     * There are two cases when alias id needs to be corrected:
     * 1) location is special location without URL alias (location with id=1 in standard installation)
     * 2) location is site root location, having special root entry in the ezurlalias_ml table (location with id=2
     *    in standard installation)
     *
     * @param mixed $locationId
     *
     * @return mixed
     */
    protected function getRealAliasId($locationId)
    {
        // Absolute root location does have a url alias entry so we can skip lookup
        if ($locationId == self::ROOT_LOCATION_ID) {
            return 0;
        }

        $data = $this->gateway->loadAutogeneratedEntry('eznode:' . $locationId);

        // Root entries (URL wise) can return 0 as the returned value is used as parent (parent is 0 for root entries)
        if (empty($data) || ($data['id'] != 0 && $data['parent'] == 0 && strlen($data['text']) == 0)) {
            $id = 0;
        } else {
            $id = $data['id'];
        }

        return $id;
    }

    /**
     * Recursively copies aliases from old parent under new parent.
     *
     * @param array $actionMap
     * @param mixed $oldParentAliasId
     * @param mixed $newParentAliasId
     */
    protected function copySubtree($actionMap, $oldParentAliasId, $newParentAliasId)
    {
        $rows = $this->gateway->loadAutogeneratedEntries($oldParentAliasId);
        $newIdsMap = [];
        foreach ($rows as $row) {
            $oldParentAliasId = $row['id'];

            // Ensure that same action entries remain grouped by the same id
            if (!isset($newIdsMap[$oldParentAliasId])) {
                $newIdsMap[$oldParentAliasId] = $this->gateway->getNextId();
            }

            $row['action'] = $actionMap[$row['action']];
            $row['parent'] = $newParentAliasId;
            $row['id'] = $row['link'] = $newIdsMap[$oldParentAliasId];
            $this->gateway->insertRow($row);

            $this->copySubtree(
                $actionMap,
                $oldParentAliasId,
                $row['id']
            );
        }
    }

    /**
     * @param mixed $oldParentId
     * @param mixed $newParentId
     *
     * @return array
     */
    protected function getCopiedLocationsMap($oldParentId, $newParentId)
    {
        $originalLocations = $this->locationGateway->getSubtreeContent($oldParentId);
        $copiedLocations = $this->locationGateway->getSubtreeContent($newParentId);

        $map = [];
        foreach ($originalLocations as $index => $originalLocation) {
            $map['eznode:' . $originalLocation['node_id']] = 'eznode:' . $copiedLocations[$index]['node_id'];
        }

        return $map;
    }

    /**
     * Notifies the underlying engine that a location was deleted or moved to trash.
     *
     * @param mixed $locationId
     */
    public function locationDeleted($locationId)
    {
        $action = 'eznode:' . $locationId;
        $entry = $this->gateway->loadAutogeneratedEntry($action);

        $this->removeSubtree($entry['id'], $action, $entry['is_original']);
    }

    /**
     * Notifies the underlying engine that Locations Content Translation was removed.
     *
     * @param int[] $locationIds all Locations of the Content that got Translation removed
     * @param string $languageCode language code of the removed Translation
     */
    public function translationRemoved(array $locationIds, $languageCode)
    {
        $languageId = $this->languageHandler->loadByLanguageCode($languageCode)->id;

        $actions = [];
        foreach ($locationIds as $locationId) {
            $actions[] = 'eznode:' . $locationId;
        }
        $this->gateway->bulkRemoveTranslation($languageId, $actions);
    }

    /**
     * Recursively removes aliases by given $id and $action.
     *
     * $original parameter is used to limit removal of moved Location aliases to history entries only.
     *
     * @param mixed $id
     * @param string $action
     * @param mixed $original
     */
    protected function removeSubtree($id, $action, $original)
    {
        // Remove first to avoid unnecessary recursion.
        if ($original) {
            // If entry is original remove all for action (history and custom entries included).
            $this->gateway->remove($action);
        } else {
            // Else entry is history, so remove only for action with the id.
            // This means $id grouped history entries are removed, other history, active autogenerated
            // and custom are left alone.
            $this->gateway->remove($action, $id);
        }

        // Load all autogenerated for parent $id, including history.
        $entries = $this->gateway->loadAutogeneratedEntries($id, true);

        foreach ($entries as $entry) {
            $this->removeSubtree($entry['id'], $entry['action'], $entry['is_original']);
        }
    }

    /**
     * @param string $text
     *
     * @return string
     */
    protected function getHash($text)
    {
        return md5(mb_strtolower($text, 'UTF-8'));
    }

    /**
     * {@inheritdoc}
     */
    public function archiveUrlAliasesForDeletedTranslations($locationId, $parentLocationId, array $languageCodes)
    {
        $parentId = $this->getRealAliasId($parentLocationId);

        $data = $this->gateway->loadLocationEntries($locationId);
        // filter removed Translations
        $removedLanguages = array_diff(
            $this->mapper->extractLanguageCodesFromData($data),
            $languageCodes
        );

        if (empty($removedLanguages)) {
            return;
        }

        // map languageCodes to their IDs
        $languageIds = array_map(
            function ($languageCode) {
                return $this->languageHandler->loadByLanguageCode($languageCode)->id;
            },
            $removedLanguages
        );

        $this->gateway->archiveUrlAliasesForDeletedTranslations($locationId, $parentId, $languageIds);
    }

    /**
     * Remove corrupted URL aliases (global, custom and system).
     *
     * @return int Number of removed URL aliases
     *
     * @throws \Exception
     */
    public function deleteCorruptedUrlAliases()
    {
        $this->transactionHandler->beginTransaction();
        try {
            $totalCount = $this->gateway->deleteUrlAliasesWithoutLocation();
            $totalCount += $this->gateway->deleteUrlAliasesWithoutParent();
            $totalCount += $this->gateway->deleteUrlAliasesWithBrokenLink();

            $this->transactionHandler->commit();

            return $totalCount;
        } catch (\Exception $e) {
            $this->transactionHandler->rollback();
            throw $e;
        }
    }

    /**
     * Attempt repairing auto-generated URL aliases for the given Location (including history).
     *
     * Note: it is assumed that at this point original, working, URL Alias for Location is published.
     *
     * @param int $locationId
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\BadStateException
     */
    public function repairBrokenUrlAliasesForLocation(int $locationId)
    {
        try {
            $this->gateway->repairBrokenUrlAliasesForLocation($locationId);
        } catch (\RuntimeException $e) {
            throw new BadStateException('locationId', $e->getMessage(), $e);
        }
    }
}
