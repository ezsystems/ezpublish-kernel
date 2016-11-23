<?php

/**
 * File containing the TagAwareStore class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\Proxy;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\ContentPurger;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * TagAwareStore implements all the logic for storing cache metadata regarding tags (locations, content type, ..).
 */
class TagAwareStore extends Store implements ContentPurger
{
    const TAG_CACHE_DIR = 'ez';

    /**
     * Writes a cache entry to the store for the given Request and Response.
     *
     * Existing entries are read and any that match the response are removed. This
     * method calls write with the new list of cache entries.
     *
     * @param Request  $request  A Request instance
     * @param Response $response A Response instance
     *
     * @return string The key under which the response is stored
     *
     * @throws \RuntimeException
     */
    public function write(Request $request, Response $response)
    {
        $key = parent::write($request, $response);

        // Now save tags
        $digest = $response->headers->get('X-Content-Digest');
        $tags = $response->headers->get('xkey', null, false);

        if ($response->headers->has('X-Location-Id')) {
            $tags[] = 'location-' . $response->headers->get('X-Location-Id');
        }

        foreach (array_unique($tags) as $tag) {
            if (false === $this->saveTag($tag, $digest)) {
                throw new \RuntimeException('Unable to store the cache tag meta information.');
            }
        }

        return $key;
    }

    /**
     * Save digest for the given tag.
     *
     * @param string $tag    The tag key
     * @param string $digest The digest hash to store representing the cache item.
     *
     * @return bool|void
     */
    private function saveTag($tag, $digest)
    {
        $path = $this->getTagPath($tag) . DIRECTORY_SEPARATOR . $digest;
        if (!is_dir(dirname($path)) && false === @mkdir(dirname($path), 0777, true) && !is_dir(dirname($path))) {
            return false;
        }

        $tmpFile = tempnam(dirname($path), basename($path));
        if (false === $fp = @fopen($tmpFile, 'wb')) {
            return false;
        }
        @fwrite($fp, $digest);
        @fclose($fp);

        if ($digest != file_get_contents($tmpFile)) {
            return false;
        }

        if (false === @rename($tmpFile, $path)) {
            return false;
        }

        @chmod($path, 0666 & ~umask());
    }

    /**
     * Purges data from $request.
     * If xkey or X-Location-Id (deprecated) header is present, the store will purge cache for given locationId or group of locationIds.
     * If not, regular purge by URI will occur.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return bool True if purge was successful. False otherwise
     */
    public function purgeByRequest(Request $request)
    {
        if (!$request->headers->has('X-Location-Id') && !$request->headers->has('xkey')) {
            return $this->purge($request->getUri());
        }

        // For BC with older purge code covering most use cases.
        $locationId = $request->headers->get('X-Location-Id');
        if ($locationId === '*' || $locationId === '.*') {
            return $this->purgeAllContent();
        }

        if ($request->headers->has('xkey')) {
            $tags = explode(' ', $request->headers->get('xkey'));
        } elseif ($locationId[0] === '(' && substr($locationId, -1) === ')') {
            // Deprecated: (123|456|789) => Purge for #123, #456 and #789 location IDs.
            $tags = array_map(
                function ($id) {return 'location-' . $id;},
                explode('|', substr($locationId, 1, -1))
            );
        } else {
            $tags = array('location-' . $locationId);
        }

        if (empty($tags)) {
            return false;
        }

        foreach ($tags as $tag) {
            $this->purgeByCacheTag($tag);
        }

        return true;
    }

    /**
     * Purges all cached content.
     *
     * @deprecated Use cache:clear, with multi tagging theoretically there shouldn't be need to delete all anymore from core.
     *
     * @return bool
     */
    public function purgeAllContent()
    {
        $fs = new Filesystem();
        $fs->remove($this->getTagPath());
        $fs->remove($this->getPath());
        return true;
    }

    /**
     * Purges cache for tag.
     *
     * @param string $tag
     *
     * @return bool
     */
    private function purgeByCacheTag($tag)
    {
        $cacheTagsCacheDir = $this->getTagPath($tag);
        if (!file_exists($cacheTagsCacheDir) || !is_dir($cacheTagsCacheDir)) {
            return false;
        }

        $files = (new Finder())->files()->in($cacheTagsCacheDir);
        foreach ($files as $file) {
            // @todo Change to be able to reuse parent::invalidate() or parent::purge() ?
            if ($digest = file_get_contents($file->getRealPath())) {
                @unlink($this->getPath($digest));
            }
            @unlink($file);
        }

        // We let folder stay in case another process have just written new cache tags.
        return true;
    }

    /**
     * Returns cache dir for $tag.
     *
     * This method is public only for unit tests.
     * Use it only if you know what you are doing.
     *
     * @internal
     *
     * @param int $tag
     *
     * @return string
     */
    public function getTagPath($tag = null)
    {
        $path = $this->root . DIRECTORY_SEPARATOR . static::TAG_CACHE_DIR;
        if ($tag) {
            // Flip the tag so we put id first so it gets sliced into folders.
            // (otherwise we would easily reach inode limits on file system)
            $tag = strrev($tag);
            $path .= DIRECTORY_SEPARATOR . substr($tag, 0, 2) . DIRECTORY_SEPARATOR . substr($tag, 2, 2) . DIRECTORY_SEPARATOR . substr($tag, 4);
        }

        return $path;
    }
}
