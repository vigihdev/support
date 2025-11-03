<?php

declare(strict_types=1);

namespace Vigihdev\Support\Tests;

use Vigihdev\Support\File;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

final class FileTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = __DIR__ . '/temp_test_files';
        if (! File::exists($this->tempDir)) {
            File::makeDirectory($this->tempDir);
        }
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->tempDir);
        parent::tearDown();
    }

    public function testGet(): void
    {
        $filePath = $this->tempDir . '/test.txt';
        File::put($filePath, 'Hello World');
        self::assertEquals('Hello World', File::get($filePath));
    }

    public function testGetThrowsExceptionForMissingFile(): void
    {
        $this->expectException(InvalidArgumentException::class);
        File::get($this->tempDir . '/non_existent.txt');
    }

    public function testPut(): void
    {
        $filePath = $this->tempDir . '/put.txt';
        $bytesWritten = File::put($filePath, 'Test Content');
        self::assertIsInt($bytesWritten);
        self::assertEquals('Test Content', file_get_contents($filePath));
    }

    public function testAppend(): void
    {
        $filePath = $this->tempDir . '/append.txt';
        File::put($filePath, 'Initial');
        File::append($filePath, ' Appended');
        self::assertEquals('Initial Appended', file_get_contents($filePath));
    }

    public function testExists(): void
    {
        $filePath = $this->tempDir . '/exists.txt';
        File::put($filePath, 'dummy');
        self::assertTrue(File::exists($filePath));
        self::assertFalse(File::exists($this->tempDir . '/non_existent.txt'));
        self::assertTrue(File::exists($this->tempDir)); // Directory exists
    }

    public function testMissing(): void
    {
        $filePath = $this->tempDir . '/missing.txt';
        self::assertTrue(File::missing($filePath));
        File::put($filePath, 'dummy');
        self::assertFalse(File::missing($filePath));
    }

    public function testDelete(): void
    {
        $filePath1 = $this->tempDir . '/delete1.txt';
        $filePath2 = $this->tempDir . '/delete2.txt';
        File::put($filePath1, 'dummy');
        File::put($filePath2, 'dummy');

        self::assertTrue(File::delete($filePath1));
        self::assertFalse(File::exists($filePath1));

        self::assertTrue(File::delete([$filePath2, $this->tempDir . '/non_existent.txt']));
        self::assertFalse(File::exists($filePath2));
    }

    public function testMove(): void
    {
        $filePath = $this->tempDir . '/move_source.txt';
        $targetPath = $this->tempDir . '/move_target.txt';
        File::put($filePath, 'Move Me');

        self::assertTrue(File::move($filePath, $targetPath));
        self::assertFalse(File::exists($filePath));
        self::assertTrue(File::exists($targetPath));
        self::assertEquals('Move Me', File::get($targetPath));
    }

    public function testCopy(): void
    {
        $filePath = $this->tempDir . '/copy_source.txt';
        $targetPath = $this->tempDir . '/copy_target.txt';
        File::put($filePath, 'Copy Me');

        self::assertTrue(File::copy($filePath, $targetPath));
        self::assertTrue(File::exists($filePath)); // Original still exists
        self::assertTrue(File::exists($targetPath));
        self::assertEquals('Copy Me', File::get($targetPath));
    }

    public function testSize(): void
    {
        $filePath = $this->tempDir . '/size.txt';
        File::put($filePath, '12345');
        self::assertEquals(5, File::size($filePath));
        self::assertFalse(File::size($this->tempDir . '/non_existent.txt'));
    }

    public function testExtension(): void
    {
        self::assertEquals('txt', File::extension($this->tempDir . '/file.txt'));
        self::assertEquals('jpg', File::extension($this->tempDir . '/image.jpg'));
        self::assertEquals('', File::extension($this->tempDir . '/noextension'));
    }

    public function testName(): void
    {
        self::assertEquals('file', File::name($this->tempDir . '/file.txt'));
        self::assertEquals('image', File::name($this->tempDir . '/image.jpg'));
        self::assertEquals('noextension', File::name($this->tempDir . '/noextension'));
    }

    public function testBasename(): void
    {
        self::assertEquals('file.txt', File::basename($this->tempDir . '/file.txt'));
        self::assertEquals('image.jpg', File::basename($this->tempDir . '/image.jpg'));
        self::assertEquals('noextension', File::basename($this->tempDir . '/noextension'));
    }

    public function testDirname(): void
    {
        $path = $this->tempDir . '/subdir/file.txt';
        self::assertEquals($this->tempDir . '/subdir', File::dirname($path));
        self::assertEquals($this->tempDir, File::dirname($this->tempDir . '/file.txt'));
    }

    public function testType(): void
    {
        $filePath = $this->tempDir . '/file.txt';
        File::put($filePath, 'dummy');
        self::assertEquals('file', File::type($filePath));
        self::assertEquals('dir', File::type($this->tempDir));
    }

    public function testMimeType(): void
    {
        $filePath = $this->tempDir . '/test.txt';
        File::put($filePath, 'Hello World');
        self::assertEquals('text/plain', File::mimeType($filePath));
    }

    public function testLastModified(): void
    {
        $filePath = $this->tempDir . '/modified.txt';
        File::put($filePath, 'dummy');
        $time = File::lastModified($filePath);
        self::assertIsInt($time);
        self::assertGreaterThan(time() - 5, $time); // Should be very recent
    }

    public function testMakeDirectory(): void
    {
        $newDir = $this->tempDir . '/new_dir';
        self::assertTrue(File::makeDirectory($newDir));
        self::assertTrue(File::exists($newDir));
        self::assertTrue(is_dir($newDir));

        // Recursive
        $nestedDir = $this->tempDir . '/parent/child';
        self::assertTrue(File::makeDirectory($nestedDir, 0755, true));
        self::assertTrue(File::exists($nestedDir));

        // Force (should not fail if exists)
        self::assertTrue(File::makeDirectory($newDir, 0755, false, true));
    }

    public function testDeleteDirectory(): void
    {
        $dirToDelete = $this->tempDir . '/dir_to_delete';
        File::makeDirectory($dirToDelete . '/subdir', 0755, true);
        File::put($dirToDelete . '/file.txt', 'dummy');

        self::assertTrue(File::deleteDirectory($dirToDelete));
        self::assertFalse(File::exists($dirToDelete));

        // Test non-existent directory
        self::assertTrue(File::deleteDirectory($this->tempDir . '/non_existent_dir'));
    }

    public function testCleanDirectory(): void
    {
        $dirToClean = $this->tempDir . '/dir_to_clean';
        File::makeDirectory($dirToClean . '/subdir', 0755, true);
        File::put($dirToClean . '/file.txt', 'dummy');

        self::assertTrue(File::cleanDirectory($dirToClean));
        self::assertTrue(File::exists($dirToClean)); // Directory itself should remain
        self::assertEmpty(File::files($dirToClean));
        self::assertEmpty(File::directories($dirToClean));
    }

    public function testFiles(): void
    {
        $dir = $this->tempDir . '/files_test';
        File::makeDirectory($dir);
        File::put($dir . '/a.txt', 'a');
        File::put($dir . '/b.txt', 'b');
        File::makeDirectory($dir . '/subdir');

        $files = File::files($dir);
        self::assertCount(2, $files);
        self::assertContains($dir . '/a.txt', $files);
        self::assertContains($dir . '/b.txt', $files);
    }

    public function testDirectories(): void
    {
        $dir = $this->tempDir . '/dirs_test';
        File::makeDirectory($dir);
        File::makeDirectory($dir . '/dir1');
        File::makeDirectory($dir . '/dir2');
        File::put($dir . '/file.txt', 'file');

        $directories = File::directories($dir);
        self::assertCount(2, $directories);
        self::assertContains($dir . '/dir1', $directories);
        self::assertContains($dir . '/dir2', $directories);
    }

    public function testAllFiles(): void
    {
        $dir = $this->tempDir . '/all_files_test';
        File::makeDirectory($dir . '/subdir1/subsubdir', 0755, true);
        File::put($dir . '/file1.txt', '1');
        File::put($dir . '/subdir1/file2.txt', '2');
        File::put($dir . '/subdir1/subsubdir/file3.txt', '3');

        $allFiles = File::allFiles($dir);
        self::assertCount(3, $allFiles);
        self::assertContains($dir . '/file1.txt', $allFiles);
        self::assertContains($dir . '/subdir1/file2.txt', $allFiles);
        self::assertContains($dir . '/subdir1/subsubdir/file3.txt', $allFiles);
    }

    public function testAllDirectories(): void
    {
        $dir = $this->tempDir . '/all_dirs_test';
        File::makeDirectory($dir . '/dir1/subdir1', 0755, true);
        File::makeDirectory($dir . '/dir2');

        $allDirs = File::allDirectories($dir);
        self::assertCount(3, $allDirs);
        self::assertContains($dir . '/dir1', $allDirs);
        self::assertContains($dir . '/dir1/subdir1', $allDirs);
        self::assertContains($dir . '/dir2', $allDirs);
    }
}
