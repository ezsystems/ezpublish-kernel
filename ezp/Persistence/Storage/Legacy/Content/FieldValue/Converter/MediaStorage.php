<?php
/**
 * File containing the Media class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter;
use ezp\Persistence\Fields\Storage,
    ezp\Persistence\Content\Field,
    ezp\Persistence\Storage\Legacy\EzcDbHandler,
    ezp\Content\FieldType\Media\Value as MediaValue,
    ezp\Io\ContentType;

/**
 * Converter for Media field type external storage
 */
class MediaStorage implements Storage
{
    const MEDIA_TABLE = 'ezmedia';

    /**
     * @see \ezp\Persistence\Fields\Storage
     */
    public function storeFieldData( Field $field, array $context )
    {
        $dbHandler = $context['connection'];
        if ( $this->mediaExists( $field->id, $field->versionNo, $dbHandler ) )
            $this->update( $field, $dbHandler );
        else
            $this->insert( $field, $dbHandler );
    }

    /**
     * Populates $field value property based on the external data.
     * $field->value is a {@link ezp\Persistence\Content\FieldValue} object.
     * This value holds the data as a {@link ezp\Content\FieldType\Value} based object,
     * according to the field type (e.g. for TextLine, it will be a {@link ezp\Content\FieldType\TextLine\Value} object).
     *
     * @param \ezp\Persistence\Content\Field $field
     * @param array $context
     * @return void
     */
    public function getFieldData( Field $field, array $context )
    {
        $media = $this->fetch( $field->id, $field->versionNo, $context['connection'] );
        list( $type, $subType ) = explode( '/', $media['mime_type'] );

        $mediaValue = new MediaValue;
        $mediaValue->file = $mediaValue->getHandler()->loadFileFromContentType(
            $media['filename'],
            new ContentType( $type, $subType )
        );
        $mediaValue->file->originalFile = $mediaValue->originalFilename = $media['original_filename'];
        $mediaValue->controls = (bool)$media['controls'];
        $mediaValue->hasController = (bool)$media['has_controller'];
        $mediaValue->width = $media['width'];
        $mediaValue->height = $media['height'];
        $mediaValue->isAutoplay = (bool)$media['is_autoplay'];
        $mediaValue->isLoop = (bool)$media['is_loop'];
        $mediaValue->pluginspage = $media['pluginspage'];
        $mediaValue->quality = $media['quality'];

        $field->value->data = $mediaValue;
    }

    /**
     * @param array $fieldId
     * @param array $context
     * @return bool
     */
    public function deleteFieldData( array $fieldId, array $context )
    {

    }

    /**
     * Checks if field type has external data to deal with
     *
     * @return bool
     */
    public function hasFieldData()
    {
        return true;
    }

    /**
     * @param \ezp\Persistence\Content\Field $field
     * @param array $context
     */
    public function copyFieldData( Field $field, array $context )
    {

    }

    /**
     * @param \ezp\Persistence\Content\Field $field
     * @param array $context
     */
    public function getIndexData( Field $field, array $context )
    {

    }

    /**
     * Fetches a row in ezmedia table referenced by $fieldId and $versionNo
     *
     * @param $fieldId
     * @param $versionNo
     * @param \ezp\Persistence\Storage\Legacy\EzcDbHandler $dbHandler
     * @return void|array Hash with columns as keys or void if no entry can be found
     */
    private function fetch( $fieldId, $versionNo, EzcDbHandler $dbHandler )
    {
        $q = $dbHandler->createSelectQuery();
        $e = $q->expr;
        $q->select( '*' )
          ->from( $dbHandler->quoteTable( self::MEDIA_TABLE ) )
          ->where(
              $e->eq( 'contentobject_attribute_id', $q->bindValue( $fieldId, null, \PDO::PARAM_INT ) ),
              $e->eq( 'version', $q->bindValue( $versionNo, null, \PDO::PARAM_INT ) )
          );
        $statement = $q->prepare();
        $statement->execute();
        $rows = $statement->fetchAll( \PDO::FETCH_ASSOC );
        if ( !empty( $rows ) )
        {
            return $rows[0];
        }
    }

    /**
     * Inserts a new entry in ezmedia table with $field value data
     *
     * @param \ezp\Persistence\Content\Field $field
     * @param \ezp\Persistence\Storage\Legacy\EzcDbHandler $dbHandler
     * @return void
     */
    private function insert( Field $field, EzcDbHandler $dbHandler )
    {
        $data = $field->value->data;
        $q = $dbHandler->createInsertQuery();

        $q->insertInto(
            $dbHandler->quoteTable( self::MEDIA_TABLE )
        )->set(
            $dbHandler->quoteColumn( 'contentobject_attribute_id' ),
            $q->bindValue( $field->id, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( 'version' ),
            $q->bindValue( $field->versionNo, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( 'filename' ),
            $q->bindValue( basename( $data->file->path ) )
        )->set(
            $dbHandler->quoteColumn( 'mime_type' ),
            $q->bindValue( (string)$data->file->contentType )
        )->set(
            $dbHandler->quoteColumn( 'original_filename' ),
            $q->bindValue( $data->originalFilename )
        )->set(
            $dbHandler->quoteColumn( 'width' ),
            $q->bindValue( $data->width, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( 'height' ),
            $q->bindValue( $data->height, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( 'has_controller' ),
            $q->bindValue( (int)$data->hasController, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( 'controls' ),
            $q->bindValue( (int)$data->controls, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( 'is_autoplay' ),
            $q->bindValue( $data->isAutoplay, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( 'pluginspage' ),
            $q->bindValue( $data->pluginspage )
        )->set(
            $dbHandler->quoteColumn( 'quality' ),
            $q->bindValue( $data->quality, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( 'is_loop' ),
            $q->bindValue( (int)$data->isLoop, null, \PDO::PARAM_INT )
        );

        $stmt = $q->prepare();
        $stmt->execute();
    }

    /**
     * Updates an existing entry in ezmedia table with $field value data
     *
     * @param \ezp\Persistence\Content\Field $field
     * @param \ezp\Persistence\Storage\Legacy\EzcDbHandler $dbHandler
     * @return void
     */
    private function update( Field $field, EzcDbHandler $dbHandler )
    {
        $data = $field->value->data;
        $q = $dbHandler->createUpdateQuery();

        $q->update(
            $dbHandler->quoteTable( self::MEDIA_TABLE )
        )->set(
            $dbHandler->quoteColumn( 'filename' ),
            $q->bindValue( basename( $data->file->path ) )
        )->set(
            $dbHandler->quoteColumn( 'mime_type' ),
            $q->bindValue( (string)$data->file->contentType )
        )->set(
            $dbHandler->quoteColumn( 'original_filename' ),
            $q->bindValue( $data->originalFilename )
        )->set(
            $dbHandler->quoteColumn( 'width' ),
            $q->bindValue( $data->width, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( 'height' ),
            $q->bindValue( $data->height, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( 'has_controller' ),
            $q->bindValue( (int)$data->hasController, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( 'controls' ),
            $q->bindValue( (int)$data->controls, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( 'is_autoplay' ),
            $q->bindValue( $data->isAutoplay, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( 'pluginspage' ),
            $q->bindValue( $data->pluginspage )
        )->set(
            $dbHandler->quoteColumn( 'quality' ),
            $q->bindValue( $data->quality, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( 'is_loop' ),
            $q->bindValue( (int)$data->isLoop, null, \PDO::PARAM_INT )
        )->where(
            $q->expr->eq( $dbHandler->quoteColumn( 'ezcontentobject_attribute_id' ), $field->id ),
            $q->expr->eq( $dbHandler->quoteColumn( 'version' ), $field->versionNo )
        );

        $stmt = $q->prepare();
        $stmt->execute();
    }

    /**
     * Checks if an entry exists in ezmedia table with $fieldId and $version as keys
     *
     * @param type $fieldId
     * @param type $version
     * @param \ezp\Persistence\Storage\Legacy\EzcDbHandler $dbHandler
     * @return bool
     */
    private function mediaExists( $fieldId, $version, EzcDbHandler $dbHandler )
    {
        $q = $dbHandler->createSelectQuery();
        $e = $q->expr;
        $q->select(
              $q->alias( $e->count( '*' ), 'count' )
          )
          ->from(
              $dbHandler->quoteTable( self::MEDIA_TABLE )
          )
          ->where(
              $e->eq(
                  $dbHandler->quoteColumn( 'ezcontentobject_attribute_id' ),
                  $fieldId
              ),
              $e->eq(
                  $dbHandler->quoteColumn( 'version' ),
                  $version
              )
        );

        $stmt = $q->prepare();
        $stmt->execute();
        $rows = $stmt->fetchAll( \PDO::FETCH_ASSOC );
        if ( !empty( $rows ) || $rows[0]['count'] > 0 )
        {
            return true;
        }

        return false;
    }
}
