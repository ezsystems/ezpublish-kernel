<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Search\FieldType;

use eZ\Publish\SPI\Search\FieldType;

/**
 * Full text document field.
 */
class FullTextField extends FieldType
{
    /**
     * The type name of the facet. Has to be handled by the solr schema.
     *
     * @var string
     */
    protected $type = 'ez_fulltext';

    /**
     * Transformation rules to be used when transforming the given string.
     *
     * @var array
     */
    public $transformationRules;

    /**
     * Flag whether the string should be split by non-words.
     *
     * @var bool
     */
    public $splitFlag;

    public function __construct(array $transformationRules = [], bool $splitFlag = true)
    {
        $this->transformationRules = $transformationRules;
        $this->splitFlag = $splitFlag;

        parent::__construct();
    }
}
