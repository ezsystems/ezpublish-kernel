<?php

/**
 * File containing the DoctrineDatabase full text criterion handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\Core\Persistence\TransformationProcessor;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\Database\SelectQuery;

/**
 * Full text criterion handler.
 */
class FullText extends CriterionHandler
{
    /**
     * Full text search configuration options.
     *
     * @var array
     */
    protected $configuration = [
        // @see getStopWordThresholdValue()
        'stopWordThresholdFactor' => 0.66,
        'enableWildcards' => true,
        'commands' => [
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
        ],
    ];

    /**
     * @var int|null
     *
     * @see getStopWordThresholdValue()
     */
    private $stopWordThresholdValue;

    /**
     * Transformation processor to normalize search strings.
     *
     * @var \eZ\Publish\Core\Persistence\TransformationProcessor
     */
    protected $processor;

    /**
     * Construct from full text search configuration.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     * @param \eZ\Publish\Core\Persistence\TransformationProcessor $processor
     * @param array $configuration
     *
     * @throws InvalidArgumentException On invalid $configuration values
     */
    public function __construct(
        DatabaseHandler $dbHandler,
        TransformationProcessor $processor,
        array $configuration = []
    ) {
        parent::__construct($dbHandler);

        $this->configuration = $configuration + $this->configuration;
        $this->processor = $processor;

        if (
            $this->configuration['stopWordThresholdFactor'] < 0 ||
            $this->configuration['stopWordThresholdFactor'] > 1
        ) {
            throw new InvalidArgumentException(
                "\$configuration['stopWordThresholdFactor']",
                'Stop Word Threshold Factor needs to be between 0 and 1, got: ' . $this->configuration['stopWordThresholdFactor']
            );
        }
    }

    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return bool
     */
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof Criterion\FullText;
    }

    /**
     * Tokenize String.
     *
     * @param string $string
     *
     * @return array
     */
    protected function tokenizeString($string)
    {
        return preg_split('/[^\w|*]/u', $string, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Get single word query expression.
     *
     * Depending on the configuration of the full text search criterion
     * converter wildcards are either transformed into the respective LIKE
     * queries, or everything is just compared using equal.
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param string $token
     *
     * @return \eZ\Publish\Core\Persistence\Database\Expression
     */
    protected function getWordExpression(SelectQuery $query, $token)
    {
        if ($this->configuration['enableWildcards'] &&
             $token[0] === '*') {
            return $query->expr->like(
                $this->dbHandler->quoteColumn('word'),
                $query->bindValue('%' . substr($token, 1))
            );
        }

        if ($this->configuration['enableWildcards'] &&
             $token[strlen($token) - 1] === '*') {
            return $query->expr->like(
                $this->dbHandler->quoteColumn('word'),
                $query->bindValue(substr($token, 0, -1) . '%')
            );
        }

        return $query->expr->eq(
            $this->dbHandler->quoteColumn('word'),
            $query->bindValue($token)
        );
    }

    /**
     * Get subquery to select relevant word IDs.
     *
     * @uses ::getStopWordThresholdValue() To get threshold for words we would like to ignore in query.
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param string $string
     *
     * @return \eZ\Publish\Core\Persistence\Database\SelectQuery
     */
    protected function getWordIdSubquery(SelectQuery $query, $string)
    {
        $subQuery = $query->subSelect();
        $tokens = $this->tokenizeString(
            $this->processor->transform($string, $this->configuration['commands'])
        );
        $wordExpressions = [];
        foreach ($tokens as $token) {
            $wordExpressions[] = $this->getWordExpression($subQuery, $token);
        }

        $whereCondition = $subQuery->expr->lOr($wordExpressions);

        // If stop word threshold is below 100%, make it part of $whereCondition
        if ($this->configuration['stopWordThresholdFactor'] < 1) {
            $whereCondition = $subQuery->expr->lAnd(
                $whereCondition,
                $subQuery->expr->lt(
                    $this->dbHandler->quoteColumn('object_count'),
                    $subQuery->bindValue($this->getStopWordThresholdValue())
                )
            );
        }

        $subQuery
            ->select($this->dbHandler->quoteColumn('id'))
            ->from($this->dbHandler->quoteTable('ezsearch_word'))
            ->where($whereCondition);

        return $subQuery;
    }

    /**
     * Generate query expression for a Criterion this handler accepts.
     *
     * accept() must be called before calling this method.
     *
     * @param \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter $converter
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param array $languageSettings
     *
     * @return \eZ\Publish\Core\Persistence\Database\Expression
     */
    public function handle(
        CriteriaConverter $converter,
        SelectQuery $query,
        Criterion $criterion,
        array $languageSettings
    ) {
        $subSelect = $query->subSelect();
        $subSelect
            ->select(
                $this->dbHandler->quoteColumn('contentobject_id')
            )->from(
                $this->dbHandler->quoteTable('ezsearch_object_word_link')
            )->where(
                $query->expr->in(
                    $this->dbHandler->quoteColumn('word_id'),
                    $this->getWordIdSubquery($subSelect, $criterion->value)
                )
            );

        return $query->expr->in(
            $this->dbHandler->quoteColumn('id', 'ezcontentobject'),
            $subSelect
        );
    }

    /**
     * Returns an exact content object count threshold to ignore common terms on.
     *
     * Common terms will be skipped if used in more then a given percentage of the total amount of content
     * objects in the database. Percentage is defined by stopWordThresholdFactor configuration.
     *
     * Example: If stopWordThresholdFactor is 0.66 (66%), and a term like "the" exists in more then 66% of the content, it
     *          will ignore the phrase as it is assumed to not add any value ot the search.
     *
     * Caches the result for the instance used as we don't need this to be super accurate as it is based on percentage,
     * set by stopWordThresholdFactor.
     *
     * @return int
     */
    protected function getStopWordThresholdValue()
    {
        if ($this->stopWordThresholdValue !== null) {
            return $this->stopWordThresholdValue;
        }

        // Cached value does not exists, do a simple count query on ezcontentobject table
        $query = $this->dbHandler->createSelectQuery();
        $query
            ->select(
                $query->alias($query->expr->count('*'), 'count')
            )
            ->from($this->dbHandler->quoteTable('ezcontentobject'));

        $statement = $query->prepare();
        $statement->execute();

        // Calculate the int stopWordThresholdValue based on count (first column) * factor
        return $this->stopWordThresholdValue =
            (int)($statement->fetchColumn() * $this->configuration['stopWordThresholdFactor']);
    }
}
