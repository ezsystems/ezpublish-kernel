<?php
/**
 * File containing the EzcDatabase content gateway class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\LegacyStorage\Content\ContentGateway;
use ezp\Persistence\LegacyStorage\Content\ContentGateway,
    ezp\Persistence\LegacyStorage\Content\StorageFieldValue,
    ezp\Persistence\Content,
    ezp\Persistence\Content\Version,
    ezp\Persistence\Content\Field;

/**
 * ezcDatabase based content gateway
 */
class EzcDatabase extends ContentGateway
{
    /**
     * Inserts a new content object.
     *
     * @param Content $content
     * @return int ID
     */
    public function insertContentObject( Content $content )
    {
        throw new \RuntimeException( 'Not implemented, yet.' );
    }

    /**
     * Inserts a new version.
     *
     * @param Version $version
     * @return int ID
     */
    public function insertVersion( Version $version )
    {
        throw new \RuntimeException( 'Not implemented, yet.' );
    }

    /**
     * Inserts a new field.
     *
     * Only used when a new content object is created. After that, field IDs
     * need to stay the same, only the version number changes.
     *
     * @param Content $content
     * @param Field $field
     * @param StorageFieldValue $value
     * @return int ID
     */
    public function insertNewField( Content $content, Field $field, StorageFieldValue $value )
    {
        throw new \RuntimeException( 'Not implemented, yet.' );
    }
}
