<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor\Criterion;

use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Visitor;

class ContentTypeIdentifier extends ValueObjectVisitor
{
    /**
     * @param Visitor $visitor
     * @param Generator $generator
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $contentTypeIdentifiers = is_array($data->value) ? $data->value : [$data->value];

        $generator->startList('ContentTypeIdentifierCriterion');
        foreach ($contentTypeIdentifiers as $contentTypeIdentifier) {
            $generator->startValueElement('ContentTypeIdentifierCriterion', $contentTypeIdentifier);
            $generator->endValueElement('ContentTypeIdentifierCriterion');
        }
        $generator->endList('ContentTypeIdentifierCriterion');
    }
}
