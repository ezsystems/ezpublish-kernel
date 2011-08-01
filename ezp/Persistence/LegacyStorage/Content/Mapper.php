<?php
/**
 * File containing the Mapper class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\LegacyStorage\Content;
use ezp\Persistence\Content,
    ezp\Persistence\Content\ContentCreateStruct,
    ezp\Persistence\Content\Field,
    ezp\Persistence\Content\Version;

/**
 *
 */
class Mapper
{
    /**
     * FieldValue converter registry
     *
     * @var FieldValueConverterRegistry
     */
    protected $converterRegistry;

    /**
     * Creates a new mapper.
     *
     * @param FieldValueConverterRegistry $converterRegistry
     */
    public function __construct( FieldValueConverterRegistry $converterRegistry )
    {
        $this->converterRegistry = $converterRegistry;
    }

    /**
     * Creates a Content from the given $struct
     *
     * @param ContentCreateStruct $struct
     * @return Content
     */
    public function createContentFromCreateStruct( ContentCreateStruct $struct )
    {
        throw new \RuntimeException( 'Not implemented, yet.' );
    }

    /**
     * Creates a new version for the given $content
     *
     * @param Content $content
     * @param int $versionNo
     * @return Version
     * @todo: created, modified, initial_language_id, status, user_id?
     */
    public function createVersionForContent( Content $content, $versionNo )
    {
        throw new \RuntimeException( 'Not implemented, yet.' );
        /*
        $version = new Version();

        $version->versionNo = $versionNo;
        $version->created   = time();
        $version->modified  = $version->created;
        $version->creatorId = $content->ownerId;
        // @todo: Is draft version correct?
        $version->state     = 0;
        $version->contentId = $content->id;

        return $version;
        */
    }

    /**
     * Converts value of $field to storage value
     *
     * @param Field $field
     * @return StorageFieldValue
     */
    public function convertToStorageValue( Field $field )
    {
        throw new \RuntimeException( 'Not implemented, yet.' );
    }
}
