<?php
/**
 * File containing the EzcDatabase full text criterion handler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\LegacyStorage\Content\ContentLocatorGateway\CriterionHandler;
use ezp\Persistence\LegacyStorage\Content\ContentLocatorGateway\CriterionHandler,
    ezp\Persistence\LegacyStorage\Content\ContentLocatorGateway\CriteriaConverter,
    ezp\Persistence\Content\Criterion;

/**
 * Full text criterion handler
 */
class FullText extends CriterionHandler
{
    /**
     * Database handler
     *
     * @var \ezcDbHandler
     */
    protected $handler;

    /**
     * Full text search configuration options
     *
     * @var array
     */
    protected $configuration = array(
        'searchThresholdValue' => 20,
        'enableWildcards'      => true,
    );

    /**
     * Construct from handler handler
     *
     * @param \ezcDbHandler $handler
     * @return void
     */
    public function __construct( \ezcDbHandler $handler )
    {
        $this->handler = $handler;
    }

    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param Criterion $criterion
     * @return bool
     */
    public function accept( Criterion $criterion )
    {
        return $criterion instanceof Criterion\FullText;
    }

    /**
     * Get array of word IDs
     *
     * @param string $string
     * @return array
     */
    protected function getWordIdSubquery( $query, $string )
    {
        $subQuery = $query->subSelect();
        $subQuery
            ->select( 'id' )
            ->from( 'ezsearch_word' )
            ->where( $subQuery->expr->lAnd(
                $subQuery->expr->lOr(
                    array_map(
                        function ( $token ) use ( $subQuery )
                        {
                            if ( $token[0] === '*' )
                            {
                                return $subQuery->expr->like(
                                    'word',
                                    $subQuery->bindValue( '%' . substr( $token, 1 ) )
                                );
                            }

                            if ( $token[strlen( $token ) - 1] === '*' )
                            {
                                return $subQuery->expr->like(
                                    'word',
                                    $subQuery->bindValue( substr( $token, 0, -1 ) . '%' )
                                );
                            }

                            return $subQuery->expr->eq(
                                'word',
                                $subQuery->bindValue( $token )
                            );
                        },
                        array_filter(
                            array_map(
                                'trim',
                                preg_split( '(\p{Z})u', strtr( $string, '\'"', '' ) )
                            )
                        )
                    )
                ),
                $subQuery->expr->lt( 'object_count', $subQuery->bindValue( $this->configuration['searchThresholdValue'] ) )
            ) );
        return $subQuery;
    }

    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param CriteriaConverter $converter
     * @param \ezcQuerySelect $query
     * @param Criterion $criterion
     * @return \ezcQueryExpression
     */
    public function handle( CriteriaConverter $converter, \ezcQuerySelect $query, Criterion $criterion )
    {
        $subSelect = $query->subSelect();
        $subSelect
            ->select( 'contentobject_id' )
            ->from( 'ezsearch_object_word_link' )
            ->where(
                $query->expr->in(
                    'word_id',
                    $this->getWordIdSubquery( $subSelect, $criterion->value )
                )
            );
        return $query->expr->in( 'id', $subSelect );
    }
}

