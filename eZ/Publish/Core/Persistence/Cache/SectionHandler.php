<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Content\Section\Handler as SectionHandlerInterface;

/**
 * @see \eZ\Publish\SPI\Persistence\Content\Section\Handler
 */
class SectionHandler extends AbstractHandler implements SectionHandlerInterface
{
    private const SECTION_TAG = 'section';
    private const PREFIXED_SECTION_TAG = 'prefixed_section';
    private const PREFIXED_SECTION_WITH_BY_ID_TAG = 'prefixed_section_with_by_id';
    private const CONTENT_TAG = 'content';

    /**
     * {@inheritdoc}
     */
    public function create($name, $identifier)
    {
        $this->logger->logCall(__METHOD__, ['name' => $name, 'identifier' => $identifier]);

        return $this->persistenceHandler->sectionHandler()->create($name, $identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, $name, $identifier)
    {
        $this->logger->logCall(__METHOD__, ['section' => $id, 'name' => $name, 'identifier' => $identifier]);
        $section = $this->persistenceHandler->sectionHandler()->update($id, $name, $identifier);

        $this->cache->invalidateTags([
            $this->tagGenerator->generate(self::SECTION_TAG, [$id]),
        ]);

        return $section;
    }

    /**
     * {@inheritdoc}
     */
    public function load($id)
    {
        $cacheItem = $this->cache->getItem(
            $this->tagGenerator->generate(self::PREFIXED_SECTION_TAG, [$id])
        );

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, ['section' => $id]);
        $section = $this->persistenceHandler->sectionHandler()->load($id);

        $cacheItem->set($section);
        $cacheItem->tag([
            $this->tagGenerator->generate(self::SECTION_TAG, [$section->id]),
        ]);

        $this->cache->save($cacheItem);

        return $section;
    }

    /**
     * {@inheritdoc}
     */
    public function loadAll()
    {
        $this->logger->logCall(__METHOD__);

        return $this->persistenceHandler->sectionHandler()->loadAll();
    }

    /**
     * {@inheritdoc}
     */
    public function loadByIdentifier($identifier)
    {
        $cacheItem = $this->cache->getItem(
            $this->tagGenerator->generate(
                self::PREFIXED_SECTION_WITH_BY_ID_TAG,
                [$this->escapeForCacheKey($identifier)]
            )
        );

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, ['section' => $identifier]);
        $section = $this->persistenceHandler->sectionHandler()->loadByIdentifier($identifier);

        $cacheItem->set($section);
        $cacheItem->tag([
            $this->tagGenerator->generate(self::SECTION_TAG, [$section->id]),
        ]);

        $this->cache->save($cacheItem);

        return $section;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        $this->logger->logCall(__METHOD__, ['section' => $id]);
        $return = $this->persistenceHandler->sectionHandler()->delete($id);

        $this->cache->invalidateTags([
            $this->tagGenerator->generate(self::SECTION_TAG, [$id]),
        ]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function assign($sectionId, $contentId)
    {
        $this->logger->logCall(__METHOD__, ['section' => $sectionId, 'content' => $contentId]);
        $return = $this->persistenceHandler->sectionHandler()->assign($sectionId, $contentId);

        $this->cache->invalidateTags([
            $this->tagGenerator->generate(self::CONTENT_TAG, [$contentId]),
        ]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function assignmentsCount($sectionId)
    {
        $this->logger->logCall(__METHOD__, ['section' => $sectionId]);

        return $this->persistenceHandler->sectionHandler()->assignmentsCount($sectionId);
    }

    /**
     * {@inheritdoc}
     */
    public function policiesCount($sectionId)
    {
        $this->logger->logCall(__METHOD__, ['section' => $sectionId]);

        return $this->persistenceHandler->sectionHandler()->policiesCount($sectionId);
    }

    /**
     * {@inheritdoc}
     */
    public function countRoleAssignmentsUsingSection($sectionId)
    {
        $this->logger->logCall(__METHOD__, ['section' => $sectionId]);

        return $this->persistenceHandler->sectionHandler()->countRoleAssignmentsUsingSection($sectionId);
    }
}
