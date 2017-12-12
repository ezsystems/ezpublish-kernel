<?php

namespace eZ\Bundle\EzPublishCoreBundle\URLChecker;

interface URLHandlerInterface
{
    /**
     * Validates given list of URLs.
     *
     * @param \eZ\Publish\API\Repository\Values\URL\URL[] $urls
     * @param callable $doUpdateStatus Callable executed to update URL status
     */
    public function validate(array $urls, callable $doUpdateStatus);

    /**
     * Set handler options.
     *
     * @param array|null $options
     */
    public function setOptions(array $options = null);
}
