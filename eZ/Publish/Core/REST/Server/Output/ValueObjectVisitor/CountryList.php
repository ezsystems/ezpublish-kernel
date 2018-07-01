<?php

/**
 * File containing the CountryList ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * CountryList value object visitor.
 */
class CountryList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\CountryList $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('CountryList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('CountryList'));
        $visitor->setHeader('Accept-Patch', false);

        $generator->startList('Country');

        foreach ($data->countries as $country) {
            $generator->startObjectElement('Country', 'Country');

            $generator->startAttribute('id', $country['Alpha2']);
            $generator->endAttribute('id');

            $generator->startValueElement('name', $country['Name']);
            $generator->endValueElement('name');

            $generator->startValueElement('Alpha2', $country['Alpha2']);
            $generator->endValueElement('Alpha2');

            $generator->startValueElement('Alpha3', $country['Alpha3']);
            $generator->endValueElement('Alpha3');

            $generator->startValueElement('IDC', $country['IDC']);
            $generator->endValueElement('IDC');

            $generator->endObjectElement('Country');
        }
        $generator->endList('Country');
        $generator->endObjectElement('CountryList');
    }
}
