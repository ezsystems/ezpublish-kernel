<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\PlaceholderProvider;

use eZ\Bundle\EzPublishCoreBundle\Imagine\PlaceholderProvider;
use eZ\Publish\Core\FieldType\Image\Value as ImageValue;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Remote placeholder provider e.g. http://placekitten.com.
 */
class RemoteProvider implements PlaceholderProvider
{
    /**
     * {@inheritdoc}
     */
    public function getPlaceholder(ImageValue $value, array $options = []): string
    {
        $options = $this->resolveOptions($options);

        $path = $this->getTemporaryPath();
        $placeholderUrl = $this->getPlaceholderUrl($options['url_pattern'], $value);

        try {
            $handler = curl_init();

            curl_setopt_array($handler, [
                CURLOPT_URL => $placeholderUrl,
                CURLOPT_FILE => fopen($path, 'wb'),
                CURLOPT_TIMEOUT => $options['timeout'],
                CURLOPT_FAILONERROR => true,
            ]);

            if (curl_exec($handler) === false) {
                throw new RuntimeException("Unable to download placeholder for {$value->id} ($placeholderUrl): " . curl_error($handler));
            }
        } finally {
            curl_close($handler);
        }

        return $path;
    }

    private function getPlaceholderUrl(string $urlPattern, ImageValue $value): string
    {
        return strtr($urlPattern, [
            '%id%' => $value->id,
            '%width%' => $value->width,
            '%height%' => $value->height,
        ]);
    }

    private function getTemporaryPath(): string
    {
        return stream_get_meta_data(tmpfile())['uri'];
    }

    private function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'url_pattern' => '',
            'timeout' => 5,
        ]);
        $resolver->setAllowedTypes('url_pattern', 'string');
        $resolver->setAllowedTypes('timeout', 'int');

        return $resolver->resolve($options);
    }
}
