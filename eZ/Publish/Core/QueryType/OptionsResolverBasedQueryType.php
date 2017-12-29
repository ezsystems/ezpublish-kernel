<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\QueryType;

use eZ\Publish\API\Repository\Values\Content\Query;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * An abstract QueryType class that facilitates parameters handling.
 * It uses Symfony's [OptionsResolver](http://symfony.com/doc/current/components/options_resolver.html).
 *
 * The interface's `getQuery()` method is implemented as final in the abstract class. Instead, you need to implement the
 * `doGetQuery()` abstract method. It receives the `$parameters` array after it has been processed using the OptionsResolver.
 *
 * In addition, you must implement the `configureOptions` abstract method. It receives an OptionsResolver, and configures
 * it for the QueryType's supported parameters.
 */
abstract class OptionsResolverBasedQueryType implements QueryType
{
    /** @var OptionsResolver */
    private $resolver;

    /**
     * Configures the OptionsResolver for the QueryType.
     *
     * Example:
     * ```php
     * // type is required
     * $resolver->setRequired('type');
     * // limit is optional, and has a default value of 10
     * $resolver->setDefault('limit', 10);
     * ```
     *
     * @param OptionsResolver $optionsResolver
     */
    abstract protected function configureOptions(OptionsResolver $optionsResolver);

    /**
     * Builds and returns the Query object.
     *
     * The parameters array is processed with the OptionsResolver, meaning that it has been validated, and contains
     * the default values when applicable.
     *
     * @param array $parameters The QueryType parameters, pre-processed by the OptionsResolver
     *
     * @return Query
     */
    abstract protected function doGetQuery(array $parameters);

    final public function getSupportedParameters()
    {
        return $this->getResolver()->getDefinedOptions();
    }

    final public function getQuery(array $parameters = [])
    {
        return $this->doGetQuery(
            $this->getResolver()->resolve($parameters)
        );
    }

    /**
     * Builds the resolver, and configures it using configureOptions().
     *
     * @return OptionsResolver
     */
    private function getResolver()
    {
        if ($this->resolver === null) {
            $this->resolver = new OptionsResolver();
            $this->configureOptions($this->resolver);
        }

        return $this->resolver;
    }
}
