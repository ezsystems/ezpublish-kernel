<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\URLChecker\Handler;

use Symfony\Component\OptionsResolver\OptionsResolver;

class MailToHandler extends AbstractURLHandler
{
    const MAILTO_PATTERN = '/^mailto:(.+)@([^?]+)(\\?.*)?$/';

    /**
     * {@inheritdoc}
     */
    public function validate(array $urls)
    {
        if (!$this->options['enabled']) {
            return;
        }

        foreach ($urls as $url) {
            if (preg_match(self::MAILTO_PATTERN, $url->url, $matches)) {
                $host = trim($matches[2]);

                $this->setUrlStatus($url, checkdnsrr($host, 'MX'));
            }
        }
    }

    /**
     * Returns options resolver.
     *
     * @return OptionsResolver
     */
    protected function getOptionsResolver()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'enabled' => true,
        ]);

        $resolver->setAllowedTypes('enabled', 'bool');

        return $resolver;
    }
}
