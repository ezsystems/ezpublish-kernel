<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\URLChecker\Handler;

use eZ\Publish\API\Repository\Values\URL\URL;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HTTPHandler extends AbstractConfigResolverBasedURLHandler
{
    /**
     * {@inheritdoc}
     *
     * Based on https://www.onlineaspect.com/2009/01/26/how-to-use-curl_multi-without-blocking/
     */
    public function validate(array $urls)
    {
        $options = $this->getOptions();

        if (!$options['enabled']) {
            return;
        }

        $master = curl_multi_init();
        $handlers = [];

        // Batch size can't be larger then number of urls
        $batchSize = min(count($urls), $options['batch_size']);
        for ($i = 0; $i < $batchSize; ++$i) {
            curl_multi_add_handle(
                $master,
                $this->createCurlHandlerForUrl(
                    $urls[$i],
                    $handlers,
                    $options['connection_timeout'],
                    $options['timeout']
                )
            );
        }

        do {
            while (($execrun = curl_multi_exec($master, $running)) == CURLM_CALL_MULTI_PERFORM);

            if ($execrun != CURLM_OK) {
                break;
            }

            while ($done = curl_multi_info_read($master)) {
                $handler = $done['handle'];

                $this->doValidate($handlers[(int)$handler], $handler);

                if ($i < count($urls)) {
                    curl_multi_add_handle(
                        $master,
                        $this->createCurlHandlerForUrl(
                            $urls[$i],
                            $handlers,
                            $options['connection_timeout'],
                            $options['timeout']
                        )
                    );
                    ++$i;
                }

                curl_multi_remove_handle($master, $handler);
                curl_close($handler);
            }
        } while ($running);

        curl_multi_close($master);
    }

    /**
     * {@inheritdoc}
     */
    protected function getOptionsResolver(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'enabled' => true,
            'timeout' => 10,
            'connection_timeout' => 5,
            'batch_size' => 10,
            'ignore_certificate' => false,
        ]);

        $resolver->setAllowedTypes('enabled', 'bool');
        $resolver->setAllowedTypes('timeout', 'int');
        $resolver->setAllowedTypes('connection_timeout', 'int');
        $resolver->setAllowedTypes('batch_size', 'int');
        $resolver->setAllowedTypes('ignore_certificate', 'bool');

        return $resolver;
    }

    public function getOptions(): array
    {
        $options = $this->configResolver->getParameter('url_handler.http.options');

        return $this->getOptionsResolver()->resolve($options);
    }

    /**
     * Initialize and return a cURL session for given URL.
     *
     * @param URL $url
     * @param array $handlers
     * @param int $connectionTimeout
     * @param int $timeout
     *
     * @return resource
     */
    private function createCurlHandlerForUrl(URL $url, array &$handlers, int $connectionTimeout, int $timeout)
    {
        $options = $this->getOptions();
        $handler = curl_init();

        curl_setopt_array($handler, [
            CURLOPT_URL => $url->url,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => $connectionTimeout,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_FAILONERROR => true,
            CURLOPT_NOBODY => true,
        ]);

        if ($options['ignore_certificate']) {
            curl_setopt_array($handler, [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);
        }

        $handlers[(int)$handler] = $url;

        return $handler;
    }

    /**
     * Validate single response.
     *
     * @param URL $url
     * @param resource $handler CURL handler
     */
    private function doValidate(URL $url, $handler)
    {
        $this->setUrlStatus($url, $this->isSuccessful(curl_getinfo($handler, CURLINFO_HTTP_CODE)));
    }

    private function isSuccessful($statusCode)
    {
        return $statusCode >= 200 && $statusCode < 300;
    }
}
