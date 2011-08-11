<?php
/**
 * File containing the EzcDatabase content gateway class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Storage\Legacy\Content\Gateway;
use ezp\Persistence\Storage\Legacy\Content\Gateway,
    ezp\Persistence\Storage\Legacy\EzcDbHandler,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldValue,
    ezp\Persistence\Content,
    ezp\Persistence\Content\Version,
    ezp\Persistence\Content\Field;

/**
 * ezcDatabase based content gateway
 */
class EzcDatabase extends Gateway
{
    /**
     * Zeta Components database handler.
     *
     * @var EzcDbHandler
     */
    protected $dbHandler;

    /**
     * Creates a new gateway based on $db
     *
     * @param EzcDbHandler $db
     */
    public function __construct( EzcDbHandler $db )
    {
        $this->dbHandler = $db;
    }

    /**
     * Inserts a new content object.
     *
     * @param Content $content
     * @return int ID
     * @todo Race condition by lastInsertId()?
     * @todo Oracle sequences?
     */
    public function insertContentObject( Content $content )
    {
        $q = $this->dbHandler->createInsertQuery();
        $q->insertInto(
            $this->dbHandler->quoteIdentifier( 'ezcontentobject' )
        )->set(
            // @FIXME: Determine version?
            $this->dbHandler->quoteIdentifier( 'current_version' ),
            $q->bindValue( 1, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteIdentifier( 'name' ),
            $q->bindValue( $content->name )
        )->set(
            $this->dbHandler->quoteIdentifier( 'contentclass_id' ),
            $q->bindValue( $content->typeId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteIdentifier( 'section_id' ),
            $q->bindValue( $content->sectionId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteIdentifier( 'owner_id' ),
            $q->bindValue( $content->ownerId, null, \PDO::PARAM_INT )
        );

        $stmt = $q->prepare();
        $stmt->execute();

        return $this->dbHandler->lastInsertId();
    }

    /**
     * Inserts a new version.
     *
     * @param Version $version
     * @return int ID
     */
    public function insertVersion( Version $version )
    {
        $q = $this->dbHandler->createInsertQuery();
        $q->insertInto(
            $this->dbHandler->quoteIdentifier( 'ezcontentobject_version' )
        )->set(
            $this->dbHandler->quoteIdentifier( 'version' ),
            $q->bindValue( $version->versionNo )
        )->set(
            $this->dbHandler->quoteIdentifier( 'modified' ),
            $q->bindValue( $version->modified, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteIdentifier( 'creator_id' ),
            $q->bindValue( $version->creatorId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteIdentifier( 'created' ),
            $q->bindValue( $version->created, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteIdentifier( 'status' ),
            $q->bindValue( $version->state, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteIdentifier( 'contentobject_id' ),
            $q->bindValue( $version->contentId, null, \PDO::PARAM_INT )
        );

        $stmt = $q->prepare();
        $stmt->execute();

        return $this->dbHandler->lastInsertId();
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
        $q = $this->dbHandler->createInsertQuery();
        $q->insertInto(
            $this->dbHandler->quoteIdentifier( 'ezcontentobject_attribute' )
        )->set(
            $this->dbHandler->quoteIdentifier( 'contentobject_id' ),
            $q->bindValue( $content->id, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteIdentifier( 'contentclassattribute_id' ),
            $q->bindValue( $field->fieldDefinitionId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteIdentifier( 'data_type_string' ),
            $q->bindValue( $field->type )
        )->set(
            // @FIXME: Is language code correct?
            $this->dbHandler->quoteIdentifier( 'language_code' ),
            $q->bindValue( $field->language )
        )->set(
            $this->dbHandler->quoteIdentifier( 'version' ),
            $q->bindValue( $field->versionNo )
        )->set(
            $this->dbHandler->quoteIdentifier( 'data_float' ),
            $q->bindValue( $value->dataFloat )
        )->set(
            $this->dbHandler->quoteIdentifier( 'data_int' ),
            $q->bindValue( $value->dataInt )
        )->set(
            $this->dbHandler->quoteIdentifier( 'data_text' ),
            $q->bindValue( $value->dataText )
        )->set(
            $this->dbHandler->quoteIdentifier( 'sort_key_int' ),
            $q->bindValue( $value->sortKeyInt )
        )->set(
            $this->dbHandler->quoteIdentifier( 'sort_key_string' ),
            $q->bindValue( $value->sortKeyString )
        );

        $stmt = $q->prepare();
        $stmt->execute();

        return $this->dbHandler->lastInsertId();
    }

    /**
     * Load data for a content object
     *
     * Returns an array with the relevant data.
     *
     * @param mixed $contentId
     * @param mixed $version
     * @return array
     */
    public function load( $contentId, $version = null )
    {
    }
}
