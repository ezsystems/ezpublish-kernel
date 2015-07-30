<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\FullText class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;
use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;
use eZ\Publish\API\Repository\Values\Content\Query\CustomFieldInterface;

/**
 * Full text search criterion.
 *
 * The string provided in this criterion is matched as a full text query
 * against all indexed content objects in the storage layer.
 *
 * Normalization and querying capabilities might depend on the system
 * configuration or the used search engine and might differ. The following
 * basic query semantics are supported:
 *
 * - If multiple words are specified an AND query is performed. OR queries are
 *   not yet supported.
 *
 * - Simple wild cards are supported. If an asterisk (*) is used at the end or
 *   beginning of a word this is translated into a wild card query. Thus "fo*"
 *   would match "foo" and "foobar", for example.
 *
 * - Simple stop word removal might be applied to the words provided in the
 *   query.
 */
class FullText extends Criterion implements CriterionInterface, CustomFieldInterface
{
    /**
     * Fuzziness of the fulltext search.
     *
     * May be a value between 0. (fuzzy) and 1. (sharp).
     *
     * @var float
     */
    public $fuzziness = 1.;

    /**
     * Boost for certain fields.
     *
     * Array of boosts to apply for certain fields – the array should look like
     * this:
     *
     * <code>
     *  array(
     *      'title' => 2,
     *      …
     *  )
     * </code>
     *
     * @var array
     */
    public $boost = array();

    /**
     * Analyzer configuration.
     *
     * @TODO: Define how this could look like
     *
     * @var mixed
     */
    public $analyzers;

    /**
     * Analyzer wildcard handling configuration.
     *
     * @TODO: Define how this could look like
     *
     * @var mixed
     */
    public $wildcards;

    /**
     * Custom field definitions to query instead of default field.
     *
     * @var array
     */
    protected $customFields = array();

    public function __construct($value, array $properties = array())
    {
        parent::__construct(null, Operator::LIKE, $value);

        // Assign additional properties, ugly but with the existing constructor
        // API the only sensible way, I guess.
        foreach ($properties as $name => $value) {
            if (!isset($this->$name)) {
                throw new \InvalidArgumentException("Unknown property $name.");
            }

            $this->$name = $value;
        }
    }

    public function getSpecifications()
    {
        return array(
            new Specifications(Operator::LIKE, Specifications::FORMAT_SINGLE),
        );
    }

    public static function createFromQueryBuilder($target, $operator, $value)
    {
        return new self($value);
    }

    /**
     * Set a custom field to query.
     *
     * Set a custom field to query for a defined field in a defined type.
     *
     * @param string $type
     * @param string $field
     * @param string $customField
     */
    public function setCustomField($type, $field, $customField)
    {
        $this->customFields[$type][$field] = $customField;
    }

    /**
     * Retun custom field.
     *
     * If no custom field is set, return null
     *
     * @param string $type
     * @param string $field
     *
     * @return mixed
     */
    public function getCustomField($type, $field)
    {
        if (!isset($this->customFields[$type]) ||
             !isset($this->customFields[$type][$field])) {
            return null;
        }

        return $this->customFields[$type][$field];
    }
}
