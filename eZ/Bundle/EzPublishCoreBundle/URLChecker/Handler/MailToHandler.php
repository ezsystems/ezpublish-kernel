<?php

namespace eZ\Bundle\EzPublishCoreBundle\URLChecker\Handler;

use eZ\Bundle\EzPublishCoreBundle\URLChecker\URLHandlerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MailToHandler implements URLHandlerInterface
{
    const MAILTO_PATTERN = '/^mailto:(.+)@([^?]+)(\\?.*)?$/';

    /**
     * @var array
     */
    private $options;

    /**
     * MailToHandler constructor.
     */
    public function __construct()
    {
        $this->options = $this->getOptionsResolver()->resolve();
    }

    /**
     * {@inheritdoc}
     */
    public function validate(array $urls, callable $doUpdateStatus)
    {
        if (!$this->options['enabled']) {
            return;
        }

        foreach ($urls as $url) {
            if (preg_match(self::MAILTO_PATTERN, $url->url, $matches)) {
                $host = trim($matches[2]);

                $doUpdateStatus($url, checkdnsrr($host, 'MX'));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options = null)
    {
        $this->options = $this->getOptionsResolver()->resolve($options);
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
