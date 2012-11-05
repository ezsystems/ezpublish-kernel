<?php
/**
 * File containing the UrlAlias Handler implementation
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory;
use eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler as UrlAliasHandlerInterface,
    eZ\Publish\SPI\Persistence\Content\UrlAlias,
    eZ\Publish\Core\Base\Exceptions\NotFoundException,
    eZ\Publish\API\Repository\Exceptions\NotImplementedException;

/**
 * @see eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler
 */
class UrlAliasHandler implements UrlAliasHandlerInterface
{
    /**
     * @var Handler
     */
    protected $handler;

    /**
     * @var Backend
     */
    protected $backend;

    /**
     * Setups current handler instance with reference to Handler object that created it.
     *
     * @param Handler $handler
     * @param Backend $backend The storage engine backend
     */
    public function __construct( Handler $handler, Backend $backend )
    {
        $this->handler = $handler;
        $this->backend = $backend;
    }

    /**
     * This method creates or updates an urlalias from a new or changed content name in a language
     * (if published). It also can be used to create an alias for a new location of content.
     * On update the old alias is linked to the new one (i.e. a history alias is generated).
     *
     * $alwaysAvailable controls whether the url alias is accessible in all
     * languages.
     *
     * @param mixed $locationId
     * @param mixed $parentLocationId In case of empty( $parentLocationId ), threat as root
     * @param string $name the new name computed by the name schema or url alias schema
     * @param string $languageCode
     * @param boolean $alwaysAvailable
     *
     * @return void Does not return the UrlAlias created / updated with type URLAlias::LOCATION
     */
    public function publishUrlAliasForLocation( $locationId, $parentLocationId, $name, $languageCode, $alwaysAvailable = false )
    {
        // Get current url alias
        $list = $this->backend->find(
            'Content\\UrlAlias',
            array(
                'destination' => $locationId,
                'type' => URLAlias::LOCATION,
                'isHistory' => false,
                'isCustom' => false
            )
        );

        if ( isset( $list[1] ) )
        {
            throw new \RuntimeException( 'Found more then 1 url alias pointing to same location: ' . $locationId );
        }
        else if ( !empty( $list ) )// Mark as history and use pathData
        {
            /** @var \eZ\Publish\SPI\Persistence\Content\UrlAlias[] $list */
            $this->backend->update( 'Content\\UrlAlias', $list[0]->id, array( 'isHistory' => true ) );
            $pathData = $list[0]->pathData;
            $pathIndex = count( $pathData ) -1;
        }
        else if ( empty( $parentLocationId ) )
        {
            $pathData = array( array( 'translations' => array() ) );
            $pathIndex = 0;
        }
        else
        {
            // Get parent url alias for pathData use
            $list = $this->backend->find(
                'Content\\UrlAlias',
                array(
                    'destination' => $parentLocationId,
                    'type' => URLAlias::LOCATION,
                    'isHistory' => false,
                    'isCustom' => false
                )
            );

            if ( isset( $list[1] ) )
                throw new \RuntimeException( 'Found more then 1 url alias pointing to same location: ' . $locationId );
            else if ( empty( $list ) )
                throw new \RuntimeException( "Did not find parent '{$parentLocationId}' for location:  {$locationId}" );

            /** @var \eZ\Publish\SPI\Persistence\Content\UrlAlias[] $list */
            $pathData = $list[0]->pathData;
            $pathData[] = array( 'translations' => array() );
            $pathIndex = count( $pathData ) -1;
        }


        // Update / Set PathData
        $pathData[$pathIndex]['always-available'] = $alwaysAvailable;
        $pathData[$pathIndex]['translations'][$languageCode] = $name;

        // Saves the new url alias object
        $this->backend->create(
            'Content\\UrlAlias',
            array(
                'type' => URLAlias::LOCATION,
                'destination' => $locationId,
                'pathData' => $pathData,
                'languageCodes' => array_keys( $pathData[$pathIndex]['translations'] ),
                'alwaysAvailable' => $alwaysAvailable,
                'isHistory' => false,
                'isCustom' => false,
                'forward' => false,
            )
        );
    }

    /**
     * Create a user chosen $alias pointing to $locationId in $languageCode.
     *
     * If $languageCode is null the $alias is created in the system's default
     * language. $alwaysAvailable makes the alias available in all languages.
     *
     * @param mixed $locationId
     * @param string $path
     * @param boolean $forwarding
     * @param string|null $languageCode
     * @param boolean $alwaysAvailable
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias With $type = URLAlias::LOCATION
     */
    public function createCustomUrlAlias( $locationId, $path, $forwarding = false, $languageCode = null, $alwaysAvailable = false )
    {
        if ( $languageCode === null )
            $languageCode = 'eng-GB';// @todo Reuse settings used in Service layer here

        $path = explode( '/', $path );
        $pathData = array();
        foreach ( $path as $pathItem )
        {
            $pathData[] = array(
                'always-available' => $alwaysAvailable,
                'translations' => array( $languageCode => $pathItem )
            );
        }

        return $this->backend->create(
            'Content\\UrlAlias',
            array(
                'type' => URLAlias::LOCATION,
                'destination' => $locationId,
                'pathData' => $pathData,
                'languageCodes' => array( $languageCode ),
                'alwaysAvailable' => $alwaysAvailable,
                'isHistory' => false,
                'isCustom' => true,
                'forward' => $forwarding,
            )
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
     * @param string $resource
     * @param string $path
     * @param boolean $forwarding
     * @param string|null $languageCode
     * @param boolean $alwaysAvailable
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias With $type = URLAlias::RESOURCE
     */
    public function createGlobalUrlAlias( $resource, $path, $forwarding = false, $languageCode = null, $alwaysAvailable = false )
    {
        if ( $languageCode === null )
            $languageCode = 'eng-GB';// @todo Reuse settings used in Service layer here

        $path = explode( '/', $path );
        $pathData = array();
        foreach ( $path as $pathItem )
        {
            $pathData[] = array(
                'always-available' => $alwaysAvailable,
                'translations' => array( $languageCode => $pathItem )
            );
        }

        return $this->backend->create(
            'Content\\UrlAlias',
            array(
                'type' => URLAlias::RESOURCE,
                'destination' => $resource,
                'pathData' => $pathData,
                'languageCodes' => array( $languageCode ),
                'alwaysAvailable' => $alwaysAvailable,
                'isHistory' => false,
                'isCustom' => true,
                'forward' => $forwarding,
            )
        );
    }

    /**
     * List global aliases.
     *
     * @param string|null $languageCode
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias[]
     */
    public function listGlobalURLAliases( $languageCode = null, $offset = 0, $limit = -1 )
    {
        $filter = array(
            'type' => URLAlias::RESOURCE,
            'isHistory' => false,
            'isCustom' => true
        );

        if ( $languageCode !== null )
            $filter['languageCode'] = $languageCode;

        $list = $this->backend->find(
            'Content\\UrlAlias',
            $filter
        );

        if ( empty( $list ) || ( $offset === 0 && $limit === -1 ) )
            return $list;

        return array_slice( $list, $offset, ( $limit === -1 ? null : $limit ) );
    }

    /**
     * List of url entries of $urlType, pointing to $locationId.
     *
     * @param mixed $locationId
     * @param boolean $custom if true the user generated aliases are listed otherwise the autogenerated
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias[]
     */
    public function listURLAliasesForLocation( $locationId, $custom = false )
    {
        return $this->backend->find(
            'Content\\UrlAlias',
            array(
                'destination' => $locationId,
                'type' => URLAlias::LOCATION,
                'isCustom' => $custom
            )
        );
    }

    /**
     * Removes url aliases.
     *
     * Autogenerated aliases are not removed by this method.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\UrlAlias[] $urlAliases
     *
     * @return boolean
     */
    public function removeURLAliases( array $urlAliases )
    {
        foreach ( $urlAliases as $index => $urlAlias )
        {
            if ( !$urlAlias instanceof UrlAlias )
                throw new \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException( "\$urlAliases[$index]", 'Expected UrlAlias instance' );

            if ( !$urlAlias->isCustom )
                continue;

            $this->backend->delete( 'Content\\UrlAlias', $urlAlias->id );
        }
    }

    /**
     * Looks up a url alias for the given url
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @param string $url
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     */
    public function lookup( $url )
    {
        $paths = explode( '/', $url );

        /**
         * @var \eZ\Publish\SPI\Persistence\Content\UrlAlias[] $urlAliases
         */
        $urlAliases = array_reverse( $this->backend->find( 'Content\\UrlAlias' ), true );
        foreach ( $urlAliases as $urlAlias )
        {
            foreach ( $paths as $index => $path )
            {
                // skip if url alias does not have this depth
                if ( empty( $urlAlias->pathData[$index]['translations'] ) )
                    continue 2;

                // check path against translations in a case in-sensitive manner
                $match = false;
                foreach ( $urlAlias->pathData[$index]['translations'] as $translatedPath )
                {
                    if ( strcasecmp( $path, $translatedPath ) === 0 )
                    {
                        $match = true;
                        break;
                    }
                }

                if ( !$match )
                    continue 2;
            }

            // skip if url alias has paths on a deeper depth then what $url has
            if ( isset( $urlAlias->pathData[$index +1]['translations'] ) )
                continue;

            // This urlAlias seems to match, return it
            return $urlAlias;
        }

        throw new NotFoundException( 'UrlAlias', $url );
    }

    /**
     * Loads URL alias by given $id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @param string $id
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     */
    public function loadUrlAlias( $id )
    {
        return $this->backend->load( 'Content\\UrlAlias', $id );
    }

    /**
     * Notifies the underlying engine that a location has moved.
     *
     * This method triggers the change of the autogenerated aliases
     *
     * @param mixed $locationId
     * @param mixed $oldParentId
     * @param mixed $newParentId
     */
    public function locationMoved( $locationId, $oldParentId, $newParentId )
    {
        // Get url alias for location
        $list = $this->backend->find(
            'Content\\UrlAlias',
            array(
                'destination' => $locationId,
                'type' => URLAlias::LOCATION,
                'isHistory' => false,
                'isCustom' => false
            )
        );

        if ( isset( $list[1] ) )
            throw new \RuntimeException( 'Found more then 1 url alias pointing to same location: ' . $locationId );
        else if ( empty( $list ) )
            throw new \RuntimeException( "Did not find any url alias for location:  {$locationId}" );

        // Mark as history and use pathData form existing location
        /** @var \eZ\Publish\SPI\Persistence\Content\UrlAlias[] $list */
        $this->backend->update( 'Content\\UrlAlias', $list[0]->id, array( 'isHistory' => true ) );
        $pathItem = array_pop( $list[0]->pathData );

        // Get url alias for new parent location
        $list = $this->backend->find(
            'Content\\UrlAlias',
            array(
                'destination' => $newParentId,
                'type' => URLAlias::LOCATION,
                'isHistory' => false,
                'isCustom' => false
            )
        );

        if ( isset( $list[1] ) )
            throw new \RuntimeException( 'Found more then 1 url alias pointing to same location: ' . $newParentId );
        else if ( empty( $list ) )
            throw new \RuntimeException( "Did not find any url alias for new parent location:  {$newParentId}" );

        // Make path data based on new location and the original
        $pathData = $list[0]->pathData;
        $pathData[] = $pathItem;
        $pathIndex = count( $pathData ) -1;

         // Create the new url alias object
        $this->backend->create(
            'Content\\UrlAlias',
            array(
                'type' => URLAlias::LOCATION,
                'destination' => $locationId,
                'pathData' => $pathData,
                'languageCodes' => array_keys( $pathData[$pathIndex]['translations'] ),
                'alwaysAvailable' => $pathData[$pathIndex]['always-available'],
                'isHistory' => false,
                'isCustom' => false,
                'forward' => false,
            )
        );
    }

    /**
     * Notifies the underlying engine that a location has moved.
     *
     * This method triggers the creation of the autogenerated aliases for the copied locations
     *
     * @param mixed $locationId
     * @param mixed $oldParentId
     * @param mixed $newParentId
     */
    public function locationCopied( $locationId, $oldParentId, $newParentId )
    {
        // Get url alias for location
        $list = $this->backend->find(
            'Content\\UrlAlias',
            array(
                'destination' => $locationId,
                'type' => URLAlias::LOCATION,
                'isHistory' => false,
                'isCustom' => false
            )
        );

        if ( isset( $list[1] ) )
            throw new \RuntimeException( 'Found more then 1 url alias pointing to same location: ' . $locationId );
        else if ( empty( $list ) )
            throw new \RuntimeException( "Did not find any url alias for location:  {$locationId}" );

        // Use pathData from existing location
        /** @var \eZ\Publish\SPI\Persistence\Content\UrlAlias[] $list */
        $pathItem = array_pop( $list[0]->pathData );

        // Get url alias for new parent location
        $list = $this->backend->find(
            'Content\\UrlAlias',
            array(
                'destination' => $newParentId,
                'type' => URLAlias::LOCATION,
                'isHistory' => false,
                'isCustom' => false
            )
        );

        if ( isset( $list[1] ) )
            throw new \RuntimeException( 'Found more then 1 url alias pointing to same location: ' . $newParentId );
        else if ( empty( $list ) )
            throw new \RuntimeException( "Did not find any url alias for new parent location:  {$newParentId}" );

        // Make path data based on new location and the original
        $pathData = $list[0]->pathData;
        $pathData[] = $pathItem;
        $pathIndex = count( $pathData ) -1;

        // Create the new url alias object
        $this->backend->create(
            'Content\\UrlAlias',
            array(
                'type' => URLAlias::LOCATION,
                'destination' => $locationId,
                'pathData' => $pathData,
                'languageCodes' => array_keys( $pathData[$pathIndex]['translations'] ),
                'alwaysAvailable' => $pathData[$pathIndex]['always-available'],
                'isHistory' => false,
                'isCustom' => false,
                'forward' => false,
            )
        );
    }

    /**
     * Notifies the underlying engine that a location was deleted or moved to trash
     *
     * @param $locationId
     */
    public function locationDeleted( $locationId )
    {
        $this->backend->deleteByMatch( 'Content\\UrlAlias', array( 'destination' => $locationId ) );
    }
}
