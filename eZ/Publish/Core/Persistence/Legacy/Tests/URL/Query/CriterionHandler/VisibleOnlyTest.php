<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\URL\Query\CriterionHandler;

use eZ\Publish\API\Repository\Values\URL\Query\Criterion\VisibleOnly;
use eZ\Publish\API\Repository\Values\URL\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\Expression;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Persistence\Legacy\URL\Query\CriteriaConverter;
use eZ\Publish\Core\Persistence\Legacy\URL\Query\CriterionHandler\VisibleOnly as VisibleOnlyHandler;

class VisibleOnlyTest extends CriterionHandlerTest
{
    /**
     * {@inheritdoc}
     */
    public function testAccept()
    {
        $handler = new VisibleOnlyHandler();

        $this->assertHandlerAcceptsCriterion($handler, VisibleOnly::class);
        $this->assertHandlerRejectsCriterion($handler, Criterion::class);
    }

    /**
     * {@inheritdoc}
     */
    public function testHandle()
    {
        $expected = 'ezcontentobject_tree.is_invisible = 0';

        $basicQuery = $this->createMock(SelectQuery::class);
        $basicQuery->method('bindValue')->with(0, null, \PDO::PARAM_INT)->willReturn('0');

        $converter = $this->createMock(CriteriaConverter::class);
        $handler = new VisibleOnlyHandler();
        $criterion = new VisibleOnly();

        // tables not joined yet - handle should join them
        $query = clone $basicQuery;
        $query->expr = $this->getMockExpression(true, true, true);
        $query->method('getQuery')->willReturn('SELECT');
        $actual = $handler->handle($converter, $query, $criterion);
        $this->assertEquals($expected, $actual);

        // table link already joined, should not join again
        $query = clone $basicQuery;
        $query->expr = $this->getMockExpression(false, true, true);
        $query->method('getQuery')->willReturn('(SELECT) INNER JOIN ezurl_object_link (ON)');
        $actual = $handler->handle($converter, $query, $criterion);
        $this->assertEquals($expected, $actual);

        // table link and attribute already joined, should not join again
        $query = clone $basicQuery;
        $query->expr = $this->getMockExpression(false, false, true);
        $query->method('getQuery')->willReturn(
            '(SELECT) INNER JOIN ezurl_object_link (ON) '
            . 'INNER JOIN ezcontentobject_attribute (ON)'
        );
        $actual = $handler->handle($converter, $query, $criterion);
        $this->assertEquals($expected, $actual);

        // table link, attribute, and tree already joined, should not join again
        $query = clone $basicQuery;
        $query->expr = $this->getMockExpression(false, false, false);
        $query->method('getQuery')->willReturn(
            '(SELECT) INNER JOIN ezurl_object_link (ON) ' .
            'INNER JOIN ezcontentobject_attribute (ON) ' .
            'INNER JOIN ezcontentobject_tree (ON)'
        );
        $actual = $handler->handle($converter, $query, $criterion);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @param bool $includeLinkJoin
     * @param bool $includeAttributeJoin
     * @param bool $includeTreeJoin
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function getMockExpression(bool $includeLinkJoin, bool $includeAttributeJoin, bool $includeTreeJoin)
    {
        $execAt = 0;
        $expr = $this->createMock(Expression::class);
        if ($includeLinkJoin) {
            $expr
                ->expects($this->at($execAt++))
                ->method('eq')
                ->with('ezurl.id', 'ezurl_object_link.url_id')
                ->willReturn('ezurl.id=ezurl_object_link.url_id');
        }
        if ($includeAttributeJoin) {
            $expr
                ->expects($this->at($execAt++))
                ->method('eq')
                ->with('ezurl_object_link.contentobject_attribute_id', 'ezcontentobject_attribute.id')
                ->willReturn('ezurl_object_link.contentobject_attribute_id = ezcontentobject_attribute.id');
            $expr
                ->expects($this->at($execAt++))
                ->method('eq')
                ->with('ezurl_object_link.contentobject_attribute_version', 'ezcontentobject_attribute.version')
                ->willReturn('ezurl_object_link.contentobject_attribute_version = ezcontentobject_attribute.version');
            $expr
                ->expects($this->at($execAt++))
                ->method('lAnd')
                ->with(
                    'ezurl_object_link.contentobject_attribute_id = ezcontentobject_attribute.id',
                    'ezurl_object_link.contentobject_attribute_version = ezcontentobject_attribute.version'
                )->willReturn('ezurl_object_link.contentobject_attribute_id = ezcontentobject_attribute.id AND ezurl_object_link.contentobject_attribute_version = ezcontentobject_attribute.version');
        }

        if ($includeTreeJoin) {
            $expr
                ->expects($this->at($execAt++))
                ->method('eq')
                ->with('ezcontentobject_tree.contentobject_id', 'ezcontentobject_attribute.contentobject_id')
                ->willReturn('ezcontentobject_tree.contentobject_id = ezcontentobject_attribute.contentobject_id');
            $expr
                ->expects($this->at($execAt++))
                ->method('eq')
                ->with('ezcontentobject_tree.contentobject_version', 'ezcontentobject_attribute.version')
                ->willReturn('ezcontentobject_tree.contentobject_version = ezcontentobject_attribute.version');
            $expr
                ->expects($this->at($execAt++))
                ->method('lAnd')
                ->with(
                    'ezcontentobject_tree.contentobject_id = ezcontentobject_attribute.contentobject_id',
                    'ezcontentobject_tree.contentobject_version = ezcontentobject_attribute.version'
                )->willReturn('ezcontentobject_tree.contentobject_id = ezcontentobject_attribute.contentobject_id AND ezcontentobject_tree.contentobject_version = ezcontentobject_attribute.version');
        }

        $expr
            ->expects($this->at($execAt))
            ->method('eq')
            ->with('ezcontentobject_tree.is_invisible', '0')
            ->willReturn('ezcontentobject_tree.is_invisible = 0');

        return $expr;
    }
}
