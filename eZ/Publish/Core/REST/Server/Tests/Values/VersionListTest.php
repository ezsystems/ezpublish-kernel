<?php
/**
 * File containing VersionListTest class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\REST\Server\Tests;
use eZ\Publish\SPI\Persistence\Content\VersionInfo as PersistenceVersionInfo;

class VersionListTest extends Tests\BaseTest
{

    public function testConstructorFiltersVersionsThatShouldNotBeExposed()
    {
        $draftVersion = self::makeVersionWithStatus(PersistenceVersionInfo::STATUS_DRAFT);
        $archivedVersion = self::makeVersionWithStatus(PersistenceVersionInfo::STATUS_ARCHIVED);
        $internalDraftVersion = self::makeVersionWithStatus(PersistenceVersionInfo::STATUS_INTERNAL_DRAFT);
        $pendingVersion = self::makeVersionWithStatus(PersistenceVersionInfo::STATUS_PENDING);
        $publishedVersion = self::makeVersionWithStatus(PersistenceVersionInfo::STATUS_PUBLISHED);
        $queuedVersion = self::makeVersionWithStatus(PersistenceVersionInfo::STATUS_QUEUED);
        $rejectedVersion = self::makeVersionWithStatus(PersistenceVersionInfo::STATUS_REJECTED);
        $repeatVersion = self::makeVersionWithStatus(PersistenceVersionInfo::STATUS_REPEAT);

        $versions = array($draftVersion, $archivedVersion, $internalDraftVersion, $pendingVersion, $publishedVersion,
                $queuedVersion, $rejectedVersion, $repeatVersion);
        $path = 'some-path';

        $result = new VersionList($versions, $path);

        self::assertEquals(
            array($draftVersion, $archivedVersion, $publishedVersion),
            $result->versions
        );
    }
    
    private static function makeVersionWithStatus($status) {
        return new VersionInfo(array('status' => $status));
    }

}
 