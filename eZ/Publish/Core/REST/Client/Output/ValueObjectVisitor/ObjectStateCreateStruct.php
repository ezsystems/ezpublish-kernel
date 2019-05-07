<?php

/**
 * File containing the ObjectStateCreateStruct ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRestCommon\Output\ValueObjectVisitor;
use EzSystems\EzPlatformRestCommon\Output\Generator;
use EzSystems\EzPlatformRestCommon\Output\Visitor;

/**
 * ObjectStateCreateStruct value object visitor.
 */
class ObjectStateCreateStruct extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \EzSystems\EzPlatformRestCommon\Output\Visitor $visitor
     * @param \EzSystems\EzPlatformRestCommon\Output\Generator $generator
     * @param mixed $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('ObjectStateCreate');
        $visitor->setHeader('Content-Type', $generator->getMediaType('ObjectStateCreate'));

        $generator->startValueElement('identifier', $data->identifier);
        $generator->endValueElement('identifier');

        $generator->startValueElement('priority', $data->priority);
        $generator->endValueElement('priority');

        $generator->startValueElement('defaultLanguageCode', $data->defaultLanguageCode);
        $generator->endValueElement('defaultLanguageCode');

        $generator->startHashElement('names');

        $generator->startList('value');
        foreach ($data->names as $languageCode => $name) {
            $generator->startValueElement('value', $name, array('languageCode' => $languageCode));
            $generator->endValueElement('value');
        }
        $generator->endList('value');

        $generator->endHashElement('names');

        $generator->startHashElement('descriptions');

        foreach ($data->descriptions as $languageCode => $description) {
            $generator->startValueElement('value', $description, array('languageCode' => $languageCode));
            $generator->endValueElement('value');
        }

        $generator->endHashElement('descriptions');

        $generator->endObjectElement('ObjectStateCreate');
    }
}
