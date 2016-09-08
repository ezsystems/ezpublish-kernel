<?php

/**
 * File containing the TagAwareStore class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\Proxy;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\ContentPurger;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Finder\Finder;

/**
 * TagAwareStore implements all the logic for storing cache metadata regarding tags (locations, content type, ..).
 */
class TagAwareStore extends Store implements ContentPurger
{
    const TAG_CACHE_DIR = 'ez';

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $fs;

    /**
     * Injects a Filesystem instance
     * For unit tests only.
     *
     * @internal
     *
     * @param \Symfony\Component\Filesystem\Filesystem $fs
     */
    public function setFilesystem(Filesystem $fs)
    {
        $this->fs = $fs;
    }

    /**
     * @return \Symfony\Component\Filesystem\Filesystem
     */
    public function getFilesystem()
    {
        if (!isset($this->fs)) {
            $this->fs = new Filesystem();
        }

        return $this->fs;
    }

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
     * @internal This is almost verbatim copy of save() from parent class as it is private.
     *
     * @param string $tag    The tag key
     * @param string $digest The digest hash to store representing the cache item.
     *
     * @return bool|void
     */
    private function saveTag($tag, $digest)
    {
        $path = $this->getTagPath($tag).DIRECTORY_SEPARATOR.$digest;
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

        // Deprecated, see purgeAllContent(): Purge everything
        $locationId = $request->headers->get('X-Location-Id');
        if ($locationId === '*' || $locationId === '.*') {
            return $this->purgeAllContent();
        }

        if ($request->headers->has('xkey')) {
            $tags = explode(' ', $request->headers->get('xkey'));
        } else if ($locationId[0] === '(' && substr($locationId, -1) === ')') {
            // Deprecated: (123|456|789) => Purge for #123, #456 and #789 location IDs.
            $tags = array_map(
                function($id){return 'location-' . $id;},
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
        $cacheTagsCacheDir = $this->getTagPath();
        $this->getFilesystem()->remove($cacheTagsCacheDir);
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
        $fs = $this->getFilesystem();
        $cacheTagsCacheDir = $this->getTagPath($tag);
        if (!$fs->exists($cacheTagsCacheDir) || !is_dir($cacheTagsCacheDir)) {
            return false;
        }

        $files = (new Finder)->files()->in($cacheTagsCacheDir);
        try {
            foreach ($files as $file) {
                // @todo Load the cache and either get all tags to delete them to, or expire the cache instead of deleting
                if ($digest = file_get_contents($file->getRealPath())) {
                    $fs->remove($this->getPath($digest));
                }
            }
            $fs->remove($files);
            // we let folder stay in case another process have just written new cache tags
        } catch (IOException $e) {
            // Log the error in the standard error log and at least try to remove the lock file
            error_log($e->getMessage());

            return false;
        }
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
        $path = $this->root.DIRECTORY_SEPARATOR.static::TAG_CACHE_DIR;
        if ($tag) {
            // Flip the tag so we put id first so it gets sliced into folders.
            // (otherwise we would easily reach inode limits on file system)
            $tag = strrev($tag);
            $path .= DIRECTORY_SEPARATOR.substr($tag, 0, 2).DIRECTORY_SEPARATOR.substr($tag, 2, 2).DIRECTORY_SEPARATOR.substr($tag, 4);
        }

        return $path;
    }
}
