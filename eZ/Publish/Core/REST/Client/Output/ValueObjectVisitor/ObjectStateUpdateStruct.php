<?php

/**
 * File containing the ObjectStateUpdateStruct ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * ObjectStateUpdateStruct value object visitor.
 */
class ObjectStateUpdateStruct extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param mixed $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('ObjectStateUpdate');
        $visitor->setHeader('Content-Type', $generator->getMediaType('ObjectStateUpdate'));

        $generator->startValueElement('identifier', $data->identifier);
        $generator->endValueElement('identifier');

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

        $generator->endObjectElement('ObjectStateUpdate');
    }
}
