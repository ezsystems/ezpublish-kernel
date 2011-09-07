<?php
/**
 * File containing the content_query API stub
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage stub
 */

use ezp\Base\Repository,
    ezp\Content,
    ezp\Content\Query;

$qb = new ezp\Content\Query\Builder;

// a full criteria
$qb->addCriteria(
    $qb->fulltext->like( 'eZ Publish' ),
    $qb->urlAlias->like( '/cms/amazing/*' ),
    $qb->contentType->eq( 'blog_post' ),
    $qb->field->eq( 'author', 'community@ez.no' )
)->addSortClause(
    $qb->sort->field( 'blog_post', 'title', Query::SORT_ASC ),
    $qb->sort->dateCreated( Query::SORT_DESC )
);
$contentList = $contentService->find( $qb->getQuery() );

// Other criteria examples

// creation date
$qb->dateMetadata->between( 'created', strtotime( 'last month' ), strtotime( 'last week' ) );

// section
$qb->section->in( array( 2, 6 ) );
?>
