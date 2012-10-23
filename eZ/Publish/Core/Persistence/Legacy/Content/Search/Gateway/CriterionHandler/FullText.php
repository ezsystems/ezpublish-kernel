<?php
/**
 * File containing the EzcDatabase full text criterion handler class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriteriaConverter,
    eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor,
    eZ\Publish\Core\Persistence\Legacy\EzcDbHandler,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion,
    ezcQuerySelect;

/**
 * Full text criterion handler
 */
class FullText extends CriterionHandler
{
    /**
     * Full text search configuration options
     *
     * @var array
     */
    protected $configuration = array(
        'searchThresholdValue' => 20,
        'enableWildcards' => true,
        'commands' => array(
            'apostrophe_normalize',
            'apostrophe_to_doublequote',
            'ascii_lowercase',
            'ascii_search_cleanup',
            'cyrillic_diacritical',
            'cyrillic_lowercase',
            'cyrillic_search_cleanup',
            'cyrillic_transliterate_ascii',
            'doublequote_normalize',
            'endline_search_normalize',
            'greek_diacritical',
            'greek_lowercase',
            'greek_normalize',
            'greek_transliterate_ascii',
            'hebrew_transliterate_ascii',
            'hyphen_normalize',
            'inverted_to_normal',
            'latin1_diacritical',
            'latin1_lowercase',
            'latin1_transliterate_ascii',
            'latin-exta_diacritical',
            'latin-exta_lowercase',
            'latin-exta_transliterate_ascii',
            'latin_lowercase',
            'latin_search_cleanup',
            'latin_search_decompose',
            'math_to_ascii',
            'punctuation_normalize',
            'space_normalize',
            'special_decompose',
            'specialwords_search_normalize',
            'tab_search_normalize',
        )
    );

    /**
     * Transformation processor to normalize search strings
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor
     */
    protected $processor;

    /**
     * Construct from full text search configuration
     *
     * @param array $configuration
     * @return void
     */
    public function __construct( EzcDbHandler $dbHandler, TransformationProcessor $processor, array $configuration = array() )
    {
        parent::__construct( $dbHandler );

        $this->configuration = $configuration + $this->configuration;
        $this->processor = $processor;
    }

    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion$criterion
     * @return bool
     */
    public function accept( Criterion $criterion )
    {
        return $criterion instanceof Criterion\FullText;
    }

    /**
     * Tokenize String
     *
     * @param string $string
     * @return array
     */
    protected function tokenizeString( $string )
    {
        return array_filter(
            array_map(
                'trim',
                preg_split( '(\\p{Z})u', strtr( $string, '\'"', '' ) )
            )
        );
    }

    /**
     * Get single word query expression
     *
     * Depending on the configuration of the full text search criterion
     * converter wildcards are either transformed into the repsective LIKE
     * queries, or everything is just compared using equal.
     *
     * @param \ezcQuerySelect $query
     * @param string $token
     * @return \ezcQueryExpression
     */
    protected function getWordExpression( ezcQuerySelect $query, $token )
    {
        if ( $this->configuration['enableWildcards'] &&
             $token[0] === '*' )
        {
            return $query->expr->like(
                $this->dbHandler->quoteColumn( 'word' ),
                $query->bindValue( '%' . substr( $token, 1 ) )
            );
        }

        if ( $this->configuration['enableWildcards'] &&
             $token[strlen( $token ) - 1] === '*' )
        {
            return $query->expr->like(
                $this->dbHandler->quoteColumn( 'word' ),
                $query->bindValue( substr( $token, 0, -1 ) . '%' )
            );
        }

        return $query->expr->eq(
            $this->dbHandler->quoteColumn( 'word' ),
            $query->bindValue( $token )
        );
    }

    /**
     * Get subquery to select relevant word IDs
     *
     * @param string $string
     * @return \ezcQuerySelect
     */
    protected function getWordIdSubquery( $query, $string )
    {
        $subQuery = $query->subSelect();
        $tokens = $this->tokenizeString(
            $this->processor->transform( $string, $this->configuration['commands'] )
        );
        $wordExpressions = array();
        foreach ( $tokens as $token )
        {
            $wordExpressions[] = $this->getWordExpression( $subQuery, $token );
        }

        $subQuery
            ->select( $this->dbHandler->quoteColumn( 'id' ) )
            ->from( $this->dbHandler->quoteTable( 'ezsearch_word' ) )
            ->where(
                $subQuery->expr->lAnd(
                    $subQuery->expr->lOr( $wordExpressions ),
                    $subQuery->expr->lt(
                        $this->dbHandler->quoteColumn( 'object_count' ),
                        $subQuery->bindValue( $this->configuration['searchThresholdValue'] )
                    )
                )
            );
        return $subQuery;
    }

    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriteriaConverter$converter
     * @param \ezcQuerySelect $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion$criterion
     * @return \ezcQueryExpression
     */
    public function handle( CriteriaConverter $converter, ezcQuerySelect $query, Criterion $criterion )
    {
        $subSelect = $query->subSelect();
        $subSelect
            ->select(
                $this->dbHandler->quoteColumn( 'contentobject_id' )
            )->from(
                $this->dbHandler->quoteTable( 'ezsearch_object_word_link' )
            )->where(
                $query->expr->in(
                    $this->dbHandler->quoteColumn( 'word_id' ),
                    $this->getWordIdSubquery( $subSelect, $criterion->value )
                )
            );
        return $query->expr->in(
            $this->dbHandler->quoteColumn( 'id', 'ezcontentobject' ),
            $subSelect
        );
    }
}

