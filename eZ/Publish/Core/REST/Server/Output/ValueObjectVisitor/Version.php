<?php
/**
 * File containing the Version ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\UrlHandler,
    eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor,
    eZ\Publish\Core\REST\Common\Output\Generator,
    eZ\Publish\Core\REST\Common\Output\Visitor,
    eZ\Publish\Core\REST\Common\Output\FieldTypeSerializer,

    eZ\Publish\Core\REST\Server\Values\RelationList as RelationListValue,

    eZ\Publish\API\Repository\Values\ContentType\ContentType,
    eZ\Publish\API\Repository\Values\Content\Field;

/**
 * Version value object visitor
 */
class Version extends ValueObjectVisitor
{
    /**
     * @var \eZ\Publish\Core\REST\Common\Output\FieldTypeSerializer
     */
    protected $fieldTypeSerializer;

    /**
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     * @param \eZ\Publish\Core\REST\Common\Output\FieldTypeSerializer $fieldTypeSerializer
     */
    public function __construct( UrlHandler $urlHandler, FieldTypeSerializer $fieldTypeSerializer )
    {
        parent::__construct( $urlHandler );
        $this->fieldTypeSerializer = $fieldTypeSerializer;
    }

    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\Version $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $content = $data->content;

        $versionInfo = $content->getVersionInfo();
        $contentInfo = $versionInfo->getContentInfo();
        $contentType = $contentInfo->getContentType();

        $generator->startObjectElement( 'Version' );

        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'Version' ) );
        $visitor->setHeader( 'Accept-Patch', $generator->getMediaType( 'VersionUpdate' ) );

        $path = $data->path;
        if ( $path == null )
        {
            $path = $this->urlHandler->generate(
                'objectVersion',
                array(
                    'object' => $content->id,
                    'version' => $versionInfo->versionNo,
                )
            );
        }

        $generator->startAttribute( 'href', $path );
        $generator->endAttribute( 'href' );

        $visitor->visitValueObject( $versionInfo );

        $generator->startHashElement( 'Fields' );
        $generator->startList( 'field' );
        foreach ( $content->getFields() as $field )
        {
            $this->visitField( $generator, $contentType, $field );
        }
        $generator->endList( 'field' );
        $generator->endHashElement( 'Fields' );

        $visitor->visitValueObject(
            new RelationListValue(
                $content->getRelations(),
                $content->id,
                $versionInfo->versionNo
            )
        );

        $generator->endObjectElement( 'Version' );
    }

    /**
     * Visits a single content field and generates its content
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     */
    public function visitField( Generator $generator, ContentType $contentType, Field $field )
    {
        $generator->startHashElement( 'field' );

        $generator->startValueElement( 'id', $field->id );
        $generator->endValueElement( 'id' );

        $generator->startValueElement( 'fieldDefinitionIdentifier', $field->fieldDefIdentifier );
        $generator->endValueElement( 'fieldDefinitionIdentifier' );

        $generator->startValueElement( 'languageCode', $field->languageCode );
        $generator->endValueElement( 'languageCode' );

        $this->fieldTypeSerializer->serializeFieldValue(
            $generator,
            $contentType,
            $field
        );

        $generator->endHashElement( 'field' );
    }
}
