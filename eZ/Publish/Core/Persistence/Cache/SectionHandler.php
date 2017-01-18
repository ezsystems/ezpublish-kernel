<?php

/**
 * File containing the SectionHandler implementation.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Content\Section\Handler as SectionHandlerInterface;

/**
 * @see \eZ\Publish\SPI\Persistence\Content\Section\Handler
 *
 * @todo Consider loadAll & loadByIdentifier cache, however then loadAll() must be used
 *       by all (incl create) but update & delete to avoid doing several cache lookups.
 */
class SectionHandler extends AbstractHandler implements SectionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function create($name, $identifier)
    {
        $this->logger->logCall(__METHOD__, array('name' => $name, 'identifier' => $identifier));
        $section = $this->persistenceHandler->sectionHandler()->create($name, $identifier);
        $this->cache->getItem('section', $section->id)->set($section)->save();

        return $section;
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, $name, $identifier)
    {
        $this->logger->logCall(__METHOD__, array('section' => $id, 'name' => $name, 'identifier' => $identifier));
        $this->cache
            ->getItem('section', $id)
            ->set($section = $this->persistenceHandler->sectionHandler()->update($id, $name, $identifier))
            ->save();

        return $section;
    }

    /**
     * {@inheritdoc}
     */
    public function load($id)
    {
        $cache = $this->cache->getItem('section', $id);
        $section = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->logCall(__METHOD__, array('section' => $id));
            $cache->set($section = $this->persistenceHandler->sectionHandler()->load($id))->save();
        }

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
        $this->logger->logCall(__METHOD__, array('section' => $identifier));

        return $this->persistenceHandler->sectionHandler()->loadByIdentifier($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        $this->logger->logCall(__METHOD__, array('section' => $id));
        $return = $this->persistenceHandler->sectionHandler()->delete($id);

        $this->cache->clear('section', $id);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function assign($sectionId, $contentId)
    {
        $this->logger->logCall(__METHOD__, array('section' => $sectionId, 'content' => $contentId));
        $return = $this->persistenceHandler->sectionHandler()->assign($sectionId, $contentId);

        $this->cache->clear('content', $contentId);
        $this->cache->clear('content', 'info', $contentId);
        $this->cache->clear('content', 'info', 'remoteId');

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function assignmentsCount($sectionId)
    {
        $this->logger->logCall(__METHOD__, array('section' => $sectionId));

        return $this->persistenceHandler->sectionHandler()->assignmentsCount($sectionId);
    }

    /**
     * {@inheritdoc}
     */
    public function policiesCount($sectionId)
    {
        $this->logger->logCall(__METHOD__, array('section' => $sectionId));

        return $this->persistenceHandler->sectionHandler()->policiesCount($sectionId);
    }

    /**
     * {@inheritdoc}
     */
    public function countRoleAssignmentsUsingSection($sectionId)
    {
        $this->logger->logCall(__METHOD__, array('section' => $sectionId));

        return $this->persistenceHandler->sectionHandler()->countRoleAssignmentsUsingSection($sectionId);
    }
}
