<?php
/**
 * File containing the VersionInfoStub class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs\Values\Content;

use \eZ\Publish\API\Repository\Values\Content\VersionInfo;

/**
 * Stubbed implementation of the {@link \eZ\Publish\API\Repository\Values\Content\VersionInfo}
 * class.
 *
 * @see \eZ\Publish\API\Repository\Values\Content\VersionInfo
 */
class VersionInfoStub extends VersionInfo
{
    private $repository;

    /**
     * Content of the content this version belongs to.
     *
     * @return ContentInfo
     */
    public function getContentInfo()
    {
        // TODO: Implement getContentInfo() method.
    }

    /**
     *
     * Returns the names computed from the name schema in the available languages.
     *
     * @return string[]
     */
    public function getNames()
    {
        // TODO: Implement getNames() method.
    }

    /**
     *
     * Returns the name computed from the name schema in the given language.
     * If no language is given the name in initial language of the version if present, otherwise null.
     *
     * @param string $languageCode
     *
     * @return string
     */
    public function getName( $languageCode = null )
    {
        // TODO: Implement getName() method.
    }

}