<?php

/**
 * File containing the URLAlias ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\API\Repository\Values;
use eZ\Publish\API\Repository\Values\Content\URLAlias as URLAliasValue;

/**
 * URLAlias value object visitor.
 */
class URLAlias extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\API\Repository\Values\Content\URLAlias $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('UrlAlias');
        $visitor->setHeader('Content-Type', $generator->getMediaType('UrlAlias'));
        $this->visitURLAliasAttributes($visitor, $generator, $data);
        $generator->endObjectElement('UrlAlias');
    }

    /**
     * Serializes the given $urlAliasType to a string representation.
     *
     * @param int $urlAliasType
     *
     * @return string
     */
    protected function serializeType($urlAliasType)
    {
        switch ($urlAliasType) {
            case Values\Content\URLAlias::LOCATION:
                return 'LOCATION';

            case Values\Content\URLAlias::RESOURCE:
                return 'RESOURCE';

            case Values\Content\URLAlias::VIRTUAL:
                return 'VIRTUAL';
        }

        throw new \RuntimeException("Unknown URL alias type: '{$urlAliasType}'.");
    }

    protected function visitURLAliasAttributes(Visitor $visitor, Generator $generator, URLAliasValue $data)
    {
        $generator->startAttribute(
            'href',
            $this->router->generate('ezpublish_rest_loadURLAlias', ['urlAliasId' => $data->id])
        );
        $generator->endAttribute('href');

        $generator->startAttribute('id', $data->id);
        $generator->endAttribute('id');

        $generator->startAttribute('type', $this->serializeType($data->type));
        $generator->endAttribute('type');

        if ($data->type === Values\Content\URLAlias::LOCATION) {
            $generator->startObjectElement('location', 'Location');
            $generator->startAttribute(
                'href',
                $this->router->generate('ezpublish_rest_loadLocation', ['locationPath' => $data->destination])
            );
            $generator->endAttribute('href');
            $generator->endObjectElement('location');
        } else {
            $generator->startValueElement('resource', $data->destination);
            $generator->endValueElement('resource');
        }

        $generator->startValueElement('path', $data->path);
        $generator->endValueElement('path');

        $generator->startValueElement('languageCodes', implode(',', $data->languageCodes));
        $generator->endValueElement('languageCodes');

        $generator->startValueElement(
            'alwaysAvailable',
            $this->serializeBool($generator, $data->alwaysAvailable)
        );
        $generator->endValueElement('alwaysAvailable');

        $generator->startValueElement(
            'isHistory',
            $this->serializeBool($generator, $data->isHistory)
        );
        $generator->endValueElement('isHistory');

        $generator->startValueElement(
            'forward',
            $this->serializeBool($generator, $data->forward)
        );
        $generator->endValueElement('forward');

        $generator->startValueElement(
            'custom',
            $this->serializeBool($generator, $data->isCustom)
        );
        $generator->endValueElement('custom');
    }
}
