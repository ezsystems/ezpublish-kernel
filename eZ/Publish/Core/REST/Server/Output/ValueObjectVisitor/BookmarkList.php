<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\Core\REST\Server\Values\BookmarkList as BookmarkListValue;

class BookmarkList extends ValueObjectVisitor
{
    /**
     * {@inheritdoc}
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('BookmarkList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('BookmarkList'));

        $this->visitAttributes($visitor, $generator, $data);
        $generator->endObjectElement('BookmarkList');
    }

    protected function visitAttributes(Visitor $visitor, Generator $generator, BookmarkListValue $data): void
    {
        $generator->startValueElement('count', $data->totalCount);
        $generator->endValueElement('count');

        $generator->startList('items');
        foreach ($data->items as $restLocation) {
            $generator->startObjectElement('Bookmark');

            $generator->startAttribute('_href', $this->router->generate('ezpublish_rest_isBookmarked', [
                'locationId' => $restLocation->location->id,
            ]));
            $generator->endAttribute('_href');

            $visitor->visitValueObject($restLocation);
            $generator->endObjectElement('Bookmark');
        }

        $generator->endList('items');
    }
}
