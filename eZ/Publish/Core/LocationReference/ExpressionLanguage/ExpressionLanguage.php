<?php

declare(strict_types=1);

namespace eZ\Publish\Core\LocationReference\ExpressionLanguage;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage as BaseExpressionLanguage;

final class ExpressionLanguage extends BaseExpressionLanguage
{
    public function __construct(CacheItemPoolInterface $parser = null, array $providers = [])
    {
        // prepends the default provider to let users override it
        array_unshift($providers, new ExpressionLanguageProvider());

        parent::__construct($parser, $providers);
    }
}
