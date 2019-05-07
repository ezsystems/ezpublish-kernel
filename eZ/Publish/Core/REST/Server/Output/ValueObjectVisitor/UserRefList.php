<?php

/**
 * File containing the UserRefList ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRestCommon\Output\ValueObjectVisitor;
use EzSystems\EzPlatformRestCommon\Output\Generator;
use EzSystems\EzPlatformRestCommon\Output\Visitor;

/**
 * UserRefList value object visitor.
 */
class UserRefList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \EzSystems\EzPlatformRestCommon\Output\Visitor $visitor
     * @param \EzSystems\EzPlatformRestCommon\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\UserRefList $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('UserRefList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('UserRefList'));
        //@todo Needs refactoring, disabling certain headers should not be done this way
        $visitor->setHeader('Accept-Patch', false);

        $generator->startAttribute('href', $data->path);
        $generator->endAttribute('href');

        $generator->startList('User');
        foreach ($data->users as $user) {
            $generator->startObjectElement('User');

            $generator->startAttribute('href', $this->router->generate('ezpublish_rest_loadUser', array('userId' => $user->contentInfo->id)));
            $generator->endAttribute('href');

            $generator->endObjectElement('User');
        }
        $generator->endList('User');

        $generator->endObjectElement('UserRefList');
    }
}
