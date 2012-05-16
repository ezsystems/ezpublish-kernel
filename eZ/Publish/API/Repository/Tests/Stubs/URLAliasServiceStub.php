<?php
/**
 * File containing the eZ\Publish\API\Repository\URLAliasService class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\API\Repository
 */

namespace eZ\Publish\API\Repository\Tests\Stubs;

use eZ\Publish\API\Repository\URLAliasService;
use eZ\Publish\API\Repository\Values\Content\URLAlias;
use eZ\Publish\API\Repository\Values\Content\Location;

/**
 * URLAlias service
 *
 * @example Examples/urlalias.php
 *
 * @package eZ\Publish\API\Repository
 */
class URLAliasServiceStub implements URLAliasService
{
    /**
     * Repository
     *
     * @var eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub
     */
    private $repository;

    /**
     * URL aliases
     *
     * @var eZ\Publish\API\Repository\Values\URLAlias
     */
    private $aliases = array();

    /**
     * Next ID to give to a new alias
     *
     * @var int
     */
    private $nextAliasId = 0;

    /**
     * Creates a new URLServiceStub
     *
     * @param RepositoryStub $repository
     * @return void
     */
    public function __construct( RepositoryStub $repository )
    {
        $this->repository = $repository;
        $this->initFromFixture();
    }

     /**
     * Create a user chosen $alias pointing to $location in $languageCode.
     *
     * This method runs URL filters and transformers before storing them.
     * Hence the path returned in the URLAlias Value may differ from the given.
     * $alwaysAvailable makes the alias available in all languages.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param string $path
     * @param boolean $forward if true a redirect is performed
     * @param string $languageCode the languageCode for which this alias is valid
     * @param boolean $alwaysAvailable
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the path already exists for the given language
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    public function createUrlAlias( Location $location, $path, $languageCode, $forwarding = false, $alwaysAvailable = false )
    {
        $this->checkAliasNotExists( $path, $languageCode );

        $data = array(
            'id'              => ++$this->nextAliasId,
            'type'            => URLAlias::LOCATION,
            'destination'     => $location,
            'path'            => $path,
            'languageCodes'   => array( $languageCode ),
            'alwaysAvailable' => $alwaysAvailable,
            'isHistory'       => false,
            'forward'         => $forwarding,
        );
        return ( $this->aliases[$data['id']] = new URLAlias( $data ) );
    }

    /**
     * Checks if an alias for the given $path already exists.
     *
     * @param string $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the path already exists for the given language
     *
     * @return void
     */
    protected function checkAliasNotExists( $path, $languageCode )
    {
        foreach ( $this->aliases as $existingAlias )
        {
            if ( !$existingAlias->isHistory
                && $existingAlias->path == $path
                && in_array( $languageCode, $existingAlias->languageCodes ) )
            {
                throw new Exceptions\InvalidArgumentExceptionStub(
                    sprintf(
                        'An alias for path "%s" in language "%s" already exists.',
                        $path,
                        $languageCode
                    )
                );
            }
        }
    }

     /**
     * Create a user chosen $alias pointing to a resource in $languageName.
     *
     * This method does not handle location resources - if a user enters a location target
     * the createCustomUrlAlias method has to be used.
     * This method runs URL filters and and transformers before storing them.
     * Hence the path returned in the URLAlias Value may differ from the given.
     *
     * $alwaysAvailable makes the alias available in all languages.
     *
     * @param string $resource
     * @param string $path
     * @param boolean $forwarding
     * @param string $languageName
     * @param boolean $alwaysAvailable
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the path already exists for the given language
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    public function createGlobalUrlAlias( $resource, $path, $languageCode, $forward = false, $alwaysAvailable = false )
    {
        $this->checkAliasNotExists( $path, $languageCode );

        $data = array(
            'id'              => ++$this->nextAliasId,
            'type'            => URLAlias::RESOURCE,
            'destination'     => $resource,
            'path'            => $path,
            'languageCodes'   => array( $languageCode ),
            'alwaysAvailable' => $alwaysAvailable,
            'isHistory'       => false,
            'forward'         => $forward,
        );
        return ( $this->aliases[$data['id']] = new URLAlias( $data ) );
    }

     /**
     * List of url aliases pointing to $location.
     *
     * @todo may be there is a need for a function which returns one URL Alias based on a prioritized language
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param $custom if true the user generated aliases are listed otherwise the autogenerated
     * @param string $languageCode filters those which are valid for the given language
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias[]
     */
    public function listLocationAliases( Location $location, $custom = true, $languageCode = null )
    {
        $locationAliases = array();
        foreach ( $this->aliases as $existingAlias )
        {
            if ( !( $existingAlias->destination instanceof Location ) || $existingAlias->destination->id != $location->id )
            {
                continue;
            }
            if ( $languageCode !== null && !in_array( $languageCode, $existingAlias->languageCodes ) )
            {
                continue;
            }
            $locationAliases[] = $existingAlias;
        }
        return $locationAliases;
    }

    /**
     * List global aliases
     *
     * @param string $languageCode filters those which are valid for the given language
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias[]
     */
    public function listGlobalAliases( $languageCode = null, $offset = 0, $limit = -1 )
    {
        $globalAliases = array();
        foreach ( $this->aliases as $existingAlias )
        {
            if ( !is_string( $existingAlias->destination ) )
            {
                continue;
            }
            if ( $languageCode !== null && !in_array( $languageCode, $existingAlias->languageCodes ) )
            {
                continue;
            }
            $globalAliases[] = $existingAlias;
        }

        return array_slice( $globalAliases, $offset, ( $limit == -1 ? null : $limit ) );
    }


    /**
     * Removes urls aliases.
     *
     * This method does not remove autogenerated aliases for locations.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\URLAlias[] $aliasList
     * @return boolean
     */
    public function removeAliases( array $aliasList )
    {
        foreach ( $aliasList as $aliasToRemove )
        {
            foreach ( $this->aliases as $index => $existingAlias )
            {
                if ( $aliasToRemove == $existingAlias )
                {
                    // TODO: How to detect "autogenerated" aliases?
                    unset( $this->aliases[$index] );
                }
            }
        }
        return true;
    }

    /**
     * looks up the URLAlias for the given url.
     *
     *
     * @param string $url
     * @param string $languageCode
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the path does not exist or is not valid for the given language
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    public function lookUp( $url, $languageCode = null )
    {
        foreach ( $this->aliases as $existingAlias )
        {
            if ( $existingAlias->path == $url
                && ( $languageCode === null || in_array( $languageCode, $existingAlias->languageCodes ) ) )
            {
                return $existingAlias;
            }
        }
        throw new Exceptions\NotFoundExceptionStub(
            sprintf(
                'No alias for URL "%s" in language "%s" could be found.',
                $url,
                $languageCode
            )
        );
    }

    /**
     * Returns the URL alias for the given location in the given language.
     *
     * If $languageCode is null the method returns the url alias in the most prioritized language.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if no url alias exist for the given language
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param  string $languageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    public function reverseLookup( Location $location, $languageCode = null )
    {
        throw new \RuntimeException( "Not implemented, yet." );
    }

    /**
     * Internal helper method to emulate a rollback.
     *
     * @return void
     */
    public function __rollback()
    {
        $this->initFromFixture();
    }

    /**
     * Helper method that initializes some default data from an existing legacy
     * test fixture.
     *
     * @return void
     */
    private function initFromFixture()
    {
        $this->aliases     = array();
        $this->nextAliasId = 0;

        list(
            $aliases,
            $this->nextAliasId
        ) = $this->repository->loadFixture( 'URLAlias' );

        foreach ( $aliases as $alias )
        {
            $this->aliases[$alias->id] = $alias;
            $this->nextAliasId         = max( $this->nextAliasId, $alias->id );
        }
    }
}
