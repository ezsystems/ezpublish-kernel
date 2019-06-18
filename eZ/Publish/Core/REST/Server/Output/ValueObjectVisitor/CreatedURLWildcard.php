<?php

/**
 * File containing the CreatedURLWildcard ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * CreatedURLWildcard value object visitor.
 *
 * @todo coverage add unit test
 */
class CreatedURLWildcard extends URLWildcard
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\CreatedURLWildcard $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        parent::visit($visitor, $generator, $data->urlWildcard);
        $visitor->setHeader(
            'Location',
            $this->router->generate(
                'ezpublish_rest_loadURLWildcard',
                ['urlWildcardId' => $data->urlWildcard->id]
            )
        );
        $visitor->setStatus(201);
    }
}
