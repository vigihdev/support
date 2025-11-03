<?php

declare(strict_types=1);

namespace Vigihdev\Support;

use FilesystemIterator;
use InvalidArgumentException;

final class File
{
    /**
     * Get the contents of a file.
     *
     * @param  string  $path
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public static function get(string $path): string
    {
        if (static::missing($path)) {
            throw new InvalidArgumentException("File does not exist at path {$path}.");
        }

        return file_get_contents($path);
    }

    /**
     * Write the contents of a file.
     *
     * @param  string  $path
     * @param  string  $contents
     * @param  bool  $lock
     * @return int|false
     */
    public static function put(string $path, string $contents, bool $lock = false): int|false
    {
        return file_put_contents($path, $contents, $lock ? LOCK_EX : 0);
    }

    /**
     * Append to a file.
     *
     * @param  string  $path
     * @param  string  $contents
     * @return int|false
     */
    public static function append(string $path, string $contents): int|false
    {
        return file_put_contents($path, $contents, FILE_APPEND | LOCK_EX);
    }

    /**
     * Determine if a file or directory exists.
     *
     * @param  string  $path
     * @return bool
     */
    public static function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * Determine if a file or directory is missing.
     *
     * @param  string  $path
     * @return bool
     */
    public static function missing(string $path): bool
    {
        return ! static::exists($path);
    }

    /**
     * Delete the file at a given path.
     *
     * @param  string|array  $paths
     * @return bool
     */
    public static function delete(string|array $paths): bool
    {
        $paths = is_array($paths) ? $paths : [$paths];

        $success = true;

        foreach ($paths as $path) {
            try {
                if (static::exists($path) && ! @unlink($path)) {
                    $success = false;
                }
            } catch (\ErrorException $e) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Move a file to a new location.
     *
     * @param  string  $path
     * @param  string  $target
     * @return bool
     */
    public static function move(string $path, string $target): bool
    {
        return rename($path, $target);
    }

    /**
     * Copy a file to a new location.
     *
     * @param  string  $path
     * @param  string  $target
     * @return bool
     */
    public static function copy(string $path, string $target): bool
    {
        return copy($path, $target);
    }

    /**
     * Get the file size in bytes.
     *
     * @param  string  $path
     * @return int|false
     */
    public static function size(string $path): int|false
    {
        if (static::missing($path)) {
            return false;
        }

        return filesize($path);
    }

    /**
     * Get the file's extension.
     *
     * @param  string  $path
     * @return string
     */
    public static function extension(string $path): string
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Get the file name from a path.
     *
     * @param  string  $path
     * @return string
     */
    public static function name(string $path): string
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * Get the file name from a path, including the extension.
     *
     * @param  string  $path
     * @return string
     */
    public static function basename(string $path): string
    {
        return pathinfo($path, PATHINFO_BASENAME);
    }

    /**
     * Get the directory name from a path.
     *
     * @param  string  $path
     * @return string
     */
    public static function dirname(string $path): string
    {
        return pathinfo($path, PATHINFO_DIRNAME);
    }

    /**
     * Get the file type.
     *
     * @param  string  $path
     * @return string
     */
    public static function type(string $path): string
    {
        return filetype($path);
    }

    /**
     * Get the MIME type of a given file.
     *
     * @param  string  $path
     * @return string|false
     */
    public static function mimeType(string $path): string|false
    {
        return mime_content_type($path);
    }

    /**
     * Get the last modification time of a file.
     *
     * @param  string  $path
     * @return int|false
     */
    public static function lastModified(string $path): int|false
    {
        return filemtime($path);
    }

    /**
     * Create a directory.
     *
     * @param  string  $path
     * @param  int  $mode
     * @param  bool  $recursive
     * @param  bool  $force
     * @return bool
     */
    public static function makeDirectory(string $path, int $mode = 0755, bool $recursive = false, bool $force = false): bool
    {
        if ($force && static::exists($path) && is_dir($path)) {
            return true;
        }

        return mkdir($path, $mode, $recursive);
    }

    /**
     * Delete a directory.
     *
     * @param  string  $directory
     * @param  bool  $preserve
     * @return bool
     */
    public static function deleteDirectory(string $directory, bool $preserve = false): bool
    {
        if (! static::exists($directory)) {
            return true;
        }

        if (! is_dir($directory)) {
            return false;
        }

        $items = new FilesystemIterator($directory);

        foreach ($items as $item) {
            if ($item->isDir() && ! $item->isLink()) {
                static::deleteDirectory($item->getPathname());
            } else {
                static::delete($item->getPathname());
            }
        }

        if (! $preserve) {
            @rmdir($directory);
        }

        return true;
    }

    /**
     * Empty the specified directory of all files and folders.
     *
     * @param  string  $directory
     * @return bool
     */
    public static function cleanDirectory(string $directory): bool
    {
        return static::deleteDirectory($directory, true);
    }

    /**
     * Get all of the files from the given directory.
     *
     * @param  string  $directory
     * @param  bool  $hidden
     * @return array
     */
    public static function files(string $directory, bool $hidden = false): array
    {
        $iterator = static::getFilesystemIterator($directory, $hidden, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::CURRENT_AS_FILEINFO);

        $files = [];
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Get all of the directories within a given directory.
     *
     * @param  string  $directory
     * @return array
     */
    public static function directories(string $directory): array
    {
        $directories = [];

        foreach (new \FilesystemIterator($directory, \FilesystemIterator::SKIP_DOTS) as $item) {
            if ($item->isDir()) {
                $directories[] = $item->getPathname();
            }
        }

        return $directories;
    }

    /**
     * Get all of the files from the given directory (recursive).
     *
     * @param  string  $directory
     * @param  bool  $hidden
     * @return array
     */
    public static function allFiles(string $directory, bool $hidden = false): array
    {
        $files = [];

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($items as $item) {
            if ($item->isFile()) {
                if ($hidden || ! str_starts_with($item->getFilename(), '.')) {
                    $files[] = $item->getPathname();
                }
            }
        }

        return $files;
    }

    /**
     * Get all of the directories within a given directory (recursive).
     *
     * @param  string  $directory
     * @return array
     */
    public static function allDirectories(string $directory): array
    {
        $directories = [];

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir() && ! in_array($item->getFilename(), ['.', '..'])) {
                $directories[] = $item->getPathname();
            }
        }

        return $directories;
    }

    /**
     * Get a FilesystemIterator for the given directory.
     *
     * @param  string  $directory
     * @param  bool  $hidden
     * @param  int  $flags
     * @return \FilesystemIterator
     */
    protected static function getFilesystemIterator(string $directory, bool $hidden = false, int $flags = \FilesystemIterator::SKIP_DOTS): \Iterator
    {
        $iterator = new \FilesystemIterator($directory, $flags);

        if ($hidden) {
            return $iterator;
        }

        return new \CallbackFilterIterator($iterator, function (\SplFileInfo $file) {
            return ! str_starts_with($file->getFilename(), '.');
        });
    }
}
