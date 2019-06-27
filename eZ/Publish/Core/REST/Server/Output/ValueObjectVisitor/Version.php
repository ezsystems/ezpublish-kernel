<?php

/**
 * File containing the Version ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\Core\REST\Common\Output\FieldTypeSerializer;
use eZ\Publish\Core\REST\Server\Values\RelationList as RelationListValue;
use eZ\Publish\Core\REST\Server\Values\Version as VersionValue;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\Content\Field;

/**
 * Version value object visitor.
 */
class Version extends ValueObjectVisitor
{
    /** @var \eZ\Publish\Core\REST\Common\Output\FieldTypeSerializer */
    protected $fieldTypeSerializer;

    /**
     * @param \eZ\Publish\Core\REST\Common\Output\FieldTypeSerializer $fieldTypeSerializer
     */
    public function __construct(FieldTypeSerializer $fieldTypeSerializer)
    {
        $this->fieldTypeSerializer = $fieldTypeSerializer;
    }

    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\Version $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('Version');

        $visitor->setHeader('Content-Type', $generator->getMediaType('Version'));
        $visitor->setHeader('Accept-Patch', $generator->getMediaType('VersionUpdate'));
        $this->visitVersionAttributes($visitor, $generator, $data);
        $generator->endObjectElement('Version');
    }

    /**
     * Visits a single content field and generates its content.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     */
    public function visitField(Generator $generator, ContentType $contentType, Field $field)
    {
        $generator->startHashElement('field');

        $generator->startValueElement('id', $field->id);
        $generator->endValueElement('id');

        $generator->startValueElement('fieldDefinitionIdentifier', $field->fieldDefIdentifier);
        $generator->endValueElement('fieldDefinitionIdentifier');

        $generator->startValueElement('languageCode', $field->languageCode);
        $generator->endValueElement('languageCode');

        $generator->startValueElement('fieldTypeIdentifier', $field->fieldTypeIdentifier);
        $generator->endValueElement('fieldTypeIdentifier');

        $this->fieldTypeSerializer->serializeFieldValue(
            $generator,
            $contentType,
            $field
        );

        $generator->endHashElement('field');
    }

    protected function visitVersionAttributes(Visitor $visitor, Generator $generator, VersionValue $data)
    {
        $content = $data->content;

        $versionInfo = $content->getVersionInfo();
        $contentType = $data->contentType;

        $path = $data->path;
        if ($path == null) {
            $path = $this->router->generate(
                'ezpublish_rest_loadContentInVersion',
                [
                    'contentId' => $content->id,
                    'versionNumber' => $versionInfo->versionNo,
                ]
            );
        }

        $generator->startAttribute('href', $path);
        $generator->endAttribute('href');

        $visitor->visitValueObject($versionInfo);

        $generator->startHashElement('Fields');
        $generator->startList('field');
        foreach ($content->getFields() as $field) {
            $this->visitField($generator, $contentType, $field);
        }
        $generator->endList('field');
        $generator->endHashElement('Fields');

        $visitor->visitValueObject(
            new RelationListValue(
                $data->relations,
                $content->id,
                $versionInfo->versionNo
            )
        );
    }
}
