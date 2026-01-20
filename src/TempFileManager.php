<?php

declare(strict_types=1);

namespace Vigihdev\Support;

use Symfony\Component\Filesystem\{Filesystem, Path};
use Vigihdev\Support\Contracts\TempFileManagerInterface;
use Vigihdev\Support\Exceptions\TempFileManagerException;

final class TempFileManager implements TempFileManagerInterface
{

    private string $tempDir;

    private Filesystem $fs;

    /**
     * Create temp file manager.
     * 
     * @param string|null $subDir Subdirectory name to store temp files.
     */
    public function __construct(?string $subDir = null)
    {
        $this->fs = new Filesystem();

        $suffix = hash('xxh64', __FILE__);
        $suffix = $subDir ? "{$suffix}/{$subDir}" : $suffix;
        $this->tempDir = Path::join(sys_get_temp_dir(), $suffix);

        if (!$this->fs->exists($this->tempDir)) {
            $this->fs->mkdir($this->tempDir, 0755);
        }
    }

    /**
     * Get temp directory path.
     * 
     * @return string
     */
    public function getTempDir(): string
    {
        return $this->tempDir;
    }

    /**
     * Get full path to temp file.
     * 
     * @param string $filename Name of temp file.
     * @return string
     */
    public function getPath(string $filename): string
    {
        return Path::join($this->tempDir, basename($filename));
    }

    /**
     * Create temp file with content.
     * 
     * @param string $filename Name of temp file.
     * @param string $content Content of temp file.
     * @return string
     */
    public function put(string $filename, string $content): string
    {
        $target = $this->getPath($filename);
        try {
            $this->fs->dumpFile($target, $content);
            return $target;
        } catch (\Throwable $e) {
            throw TempFileManagerException::invalidPut($target);
        }
    }

    /**
     * Read content from temp file.
     * 
     * @param string $filename Name of temp file.
     * @return string
     */
    public function get(string $filename): string
    {

        $tempFile = $this->getPath($filename);
        if (!is_file($tempFile)) {
            throw TempFileManagerException::notExistFile($tempFile);
        }

        try {
            return File::get($tempFile);
        } catch (\Throwable $e) {
            throw TempFileManagerException::invalidGet($tempFile);
        }
    }

    /**
     * Delete temp file.
     * 
     * @param string $filename Name of temp file.
     * @return bool
     */
    public function delete(string $filename): bool
    {
        if (is_file($this->getPath($filename))) {
            return (bool) unlink($this->getPath($filename));
        }
        return false;
    }

    /**
     * Delete all temp files.
     * 
     * @return void
     */
    public function clearAll(): void
    {
        if ($this->fs->exists($this->tempDir)) {
            $this->fs->remove($this->tempDir);
        }
    }

    /**
     * Check if temp file exists.
     * 
     * @param string $filename Name of temp file.
     * @return bool
     */
    public function exists(string $filename): bool
    {
        return (bool)is_file($this->getPath($filename));
    }
}
