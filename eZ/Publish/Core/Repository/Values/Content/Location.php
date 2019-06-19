<?php

/**
 * File containing the eZ\Publish\Core\Repository\Values\Content\Location class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\Content\Location as APILocation;

/**
 * This class represents a location in the repository.
 *
 * @internal Meant for internal use by Repository, type hint against API object instead.
 */
class Location extends APILocation
{
    /**
     * Content info of the content object of this location.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    protected $contentInfo;

    /**
     * @var array
     */
    protected $path;

    /**
     * Returns the content info of the content object of this location.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public function getContentInfo()
    {
        return $this->contentInfo;
    }

    /**
     * Function where list of properties are returned.
     *
     * Override to add dynamic properties
     *
     * @uses \parent::getProperties()
     *
     * @param array $dynamicProperties
     *
     * @return array
     */
    protected function getProperties($dynamicProperties = ['contentId'])
    {
        return parent::getProperties($dynamicProperties);
    }

    /**
     * Magic getter for retrieving convenience properties.
     *
     * @param string $property The name of the property to retrieve
     *
     * @return mixed
     */
    public function __get($property)
    {
        switch ($property) {
            case 'contentId':
                return $this->contentInfo->id;
            case 'path':
                if ($this->path !== null) {
                    return $this->path;
                }
                if (isset($this->pathString[1]) && $this->pathString[0] === '/') {
                    return $this->path = explode('/', trim($this->pathString, '/'));
                }

                return $this->path = [];
        }

        return parent::__get($property);
    }

    /**
     * Magic isset for signaling existence of convenience properties.
     *
     * @param string $property
     *
     * @return bool
     */
    public function __isset($property)
    {
        if ($property === 'contentId' || $property === 'path') {
            return true;
        }

        return parent::__isset($property);
    }
}
