<?php

/**
 * File containing the ObjectStateGroup ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * ObjectStateGroup value object visitor.
 */
class ObjectStateGroup extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('ObjectStateGroup');
        $visitor->setHeader('Content-Type', $generator->getMediaType('ObjectStateGroup'));
        $visitor->setHeader('Accept-Patch', $generator->getMediaType('ObjectStateGroupUpdate'));

        $generator->startAttribute(
            'href',
            $this->router->generate('ezpublish_rest_loadObjectStateGroup', array('objectStateGroupId' => $data->id))
        );
        $generator->endAttribute('href');

        $generator->startValueElement('id', $data->id);
        $generator->endValueElement('id');

        $generator->startValueElement('identifier', $data->identifier);
        $generator->endValueElement('identifier');

        $generator->startValueElement('defaultLanguageCode', $data->defaultLanguageCode);
        $generator->endValueElement('defaultLanguageCode');

        $generator->startValueElement('languageCodes', implode(',', $data->languageCodes));
        $generator->endValueElement('languageCodes');

        $generator->startObjectElement('ObjectStates', 'ObjectStateList');
        $generator->startAttribute(
            'href',
            $this->router->generate('ezpublish_rest_loadObjectStates', array('objectStateGroupId' => $data->id))
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('ObjectStates');

        $this->visitNamesList($generator, $data->getNames());
        $this->visitDescriptionsList($generator, $data->getDescriptions());

        $generator->endObjectElement('ObjectStateGroup');
    }
}
