<?php

/**
 * File containing the BadRequestException ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\Core\Base\Exceptions;
use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\Exception;

/**
 * ViewInput value object visitor.
 */
class ViewInput extends ValueObjectVisitor
{
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('ViewInput');

        $generator->startValueElement('identifier', $data->identifier);
        $generator->endValueElement('identifier');

        if ($data->locationQuery !== null ) {
            $queryElementName = 'LocationQuery';
        } elseif ($data->contentQuery !== null ) {
            $queryElementName = 'ContentQuery';
        } else {
            throw new Exceptions\InvalidArgumentException("ViewInput Query", "No content nor location query found in ViewInput");
        }

        $generator->startObjectElement($queryElementName);
        $generator->endObjectElement($queryElementName);

        $generator->endObjectElement('ViewInput');
    }
}
