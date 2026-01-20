<?php

declare(strict_types=1);

namespace Vigihdev\Support\Tests;

use Vigihdev\Support\TempFileManager;
use Vigihdev\Support\Exceptions\TempFileManagerException;
use PHPUnit\Framework\TestCase;

final class TempFileManagerTest extends TestCase
{
    private TempFileManager $tempFileManager;
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempFileManager = new TempFileManager('test_subdir');
        $this->tempDir = $this->tempFileManager->getTempDir();
    }

    protected function tearDown(): void
    {
        $this->tempFileManager->clearAll();
        parent::tearDown();
    }

    public function testConstructorCreatesTempDirectory(): void
    {
        self::assertDirectoryExists($this->tempDir);
        self::assertStringContainsString('test_subdir', $this->tempDir);
    }

    public function testGetTempDirReturnsCorrectPath(): void
    {
        $tempDir = $this->tempFileManager->getTempDir();
        self::assertIsString($tempDir);
        self::assertNotEmpty($tempDir);
        self::assertDirectoryExists($tempDir);
    }

    public function testGetPathReturnsCorrectPath(): void
    {
        $filename = 'test.txt';
        $fullPath = $this->tempFileManager->getPath($filename);
        
        self::assertStringStartsWith($this->tempDir, $fullPath);
        self::assertStringEndsWith($filename, $fullPath);
        self::assertStringContainsString($filename, $fullPath);
    }

    public function testGetPathSanitizesFilename(): void
    {
        $filename = '../malicious_file.txt';
        $fullPath = $this->tempFileManager->getPath($filename);
        
        self::assertStringStartsWith($this->tempDir, $fullPath);
        self::assertStringNotContainsString('..', $fullPath);
        self::assertStringEndsWith('malicious_file.txt', $fullPath);
    }

    public function testPutCreatesFileAndReturnsPath(): void
    {
        $filename = 'put_test.txt';
        $content = 'Hello World!';
        
        $path = $this->tempFileManager->put($filename, $content);
        
        self::assertFileExists($path);
        self::assertEquals($content, file_get_contents($path));
        self::assertStringEndsWith($filename, $path);
    }

    public function testGetReturnsFileContent(): void
    {
        $filename = 'get_test.txt';
        $content = 'Test Content';
        
        $this->tempFileManager->put($filename, $content);
        $retrievedContent = $this->tempFileManager->get($filename);
        
        self::assertEquals($content, $retrievedContent);
    }

    public function testGetThrowsExceptionForNonExistentFile(): void
    {
        $this->expectException(TempFileManagerException::class);
        $this->expectExceptionMessage('File not found: nonexistent.txt');
        
        $this->tempFileManager->get('nonexistent.txt');
    }

    public function testExistsReturnsTrueForExistingFile(): void
    {
        $filename = 'exists_test.txt';
        $this->tempFileManager->put($filename, 'content');
        
        self::assertTrue($this->tempFileManager->exists($filename));
    }

    public function testExistsReturnsFalseForNonExistentFile(): void
    {
        self::assertFalse($this->tempFileManager->exists('nonexistent.txt'));
    }

    public function testDeleteRemovesExistingFile(): void
    {
        $filename = 'delete_test.txt';
        $this->tempFileManager->put($filename, 'content');
        
        self::assertTrue($this->tempFileManager->exists($filename));
        
        $result = $this->tempFileManager->delete($filename);
        
        self::assertTrue($result);
        self::assertFalse($this->tempFileManager->exists($filename));
    }

    public function testDeleteReturnsFalseForNonExistentFile(): void
    {
        $result = $this->tempFileManager->delete('nonexistent.txt');
        self::assertFalse($result);
    }

    public function testClearAllRemovesAllTempFiles(): void
    {
        $filename1 = 'clear_test1.txt';
        $filename2 = 'clear_test2.txt';
        
        $this->tempFileManager->put($filename1, 'content1');
        $this->tempFileManager->put($filename2, 'content2');
        
        self::assertTrue($this->tempFileManager->exists($filename1));
        self::assertTrue($this->tempFileManager->exists($filename2));
        
        $this->tempFileManager->clearAll();
        
        self::assertDirectoryDoesNotExist($this->tempDir);
    }

    public function testMultipleInstancesWithSameSubDir(): void
    {
        $tempManager1 = new TempFileManager('same_subdir');
        $tempManager2 = new TempFileManager('same_subdir');
        
        // Both should have the same temp directory structure
        $dir1 = $tempManager1->getTempDir();
        $dir2 = $tempManager2->getTempDir();
        
        // Since they use hash of __FILE__, they should have same base but different instances
        // could still work in the same temp area
        $tempManager1->put('test1.txt', 'content1');
        $tempManager2->put('test2.txt', 'content2');
        
        self::assertTrue($tempManager1->exists('test1.txt'));
        self::assertTrue($tempManager2->exists('test2.txt'));
        
        $tempManager1->clearAll();
    }

    public function testConstructorWithoutSubDir(): void
    {
        $tempManager = new TempFileManager();
        $tempDir = $tempManager->getTempDir();
        
        self::assertDirectoryExists($tempDir);
        self::assertStringNotContainsString('/', basename($tempDir)); // No subdirectory in the final path
        
        $tempManager->clearAll();
    }
}