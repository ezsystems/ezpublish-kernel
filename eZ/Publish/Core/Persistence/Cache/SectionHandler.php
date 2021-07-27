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

        $this->cache->invalidateTags([TagIdentifiers::SECTION . '-' . $id]);

        return $section;
    }

    /**
     * {@inheritdoc}
     */
    public function load($id)
    {
        $cacheItem = $this->cache->getItem(TagIdentifiers::PREFIX . TagIdentifiers::SECTION . '-' . $id);
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, ['section' => $id]);
        $section = $this->persistenceHandler->sectionHandler()->load($id);

        $cacheItem->set($section);
        $cacheItem->tag([TagIdentifiers::SECTION . '-' . $section->id]);
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
            TagIdentifiers::PREFIX .
            TagIdentifiers::SECTION . '-' .
            $this->escapeForCacheKey($identifier) .
            TagIdentifiers::BY_IDENTIFIER_SUFFIX
        );

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, ['section' => $identifier]);
        $section = $this->persistenceHandler->sectionHandler()->loadByIdentifier($identifier);

        $cacheItem->set($section);
        $cacheItem->tag([TagIdentifiers::SECTION . '-' . $section->id]);
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

        $this->cache->invalidateTags([TagIdentifiers::SECTION . '-' . $id]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function assign($sectionId, $contentId)
    {
        $this->logger->logCall(__METHOD__, ['section' => $sectionId, 'content' => $contentId]);
        $return = $this->persistenceHandler->sectionHandler()->assign($sectionId, $contentId);

        $this->cache->invalidateTags([TagIdentifiers::CONTENT . '-' . $contentId]);

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
