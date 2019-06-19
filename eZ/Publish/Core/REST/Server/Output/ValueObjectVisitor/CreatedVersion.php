<?php

/**
 * File containing the CreatedVersion ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * CreatedVersion value object visitor.
 *
 * @todo coverage add unit test
 */
class CreatedVersion extends Version
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\CreatedVersion $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        parent::visit($visitor, $generator, $data->version);
        $visitor->setHeader(
            'Location',
            $this->router->generate(
                'ezpublish_rest_loadContentInVersion',
                [
                    'contentId' => $data->version->content->id,
                    'versionNumber' => $data->version->content->getVersionInfo()->versionNo,
                ]
            )
        );
        $visitor->setStatus(201);
    }
}
