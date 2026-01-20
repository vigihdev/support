<?php

declare(strict_types=1);

namespace Vigihdev\Support\Exceptions;


final class TempFileManagerException extends SupportException
{

    public static function notExistFile(string $filepath): self
    {
        return new self(
            message: sprintf("File not found: %s", basename($filepath)),
            context: [
                'filepath' => $filepath,
            ],
            code: 404,
            solutions: [
                'Check if temp file exists.',
                'Check filepath and ensure file exists',
                'Check file permission: chmod +r ' . basename($filepath)
            ]
        );
    }

    public static function invalidGet(string $filepath): self
    {
        return new self(
            message: sprintf("Invalid get temp file: %s", basename($filepath)),
            context: [
                'filepath' => $filepath,
            ],
            code: 400,
            solutions: [
                'Check if temp file exists.',
                'Check filepath and ensure file exists',
                'Check file permission: chmod +r ' . basename($filepath)
            ]
        );
    }

    public static function invalidDelete(string $filepath): self
    {
        return new self(
            message: sprintf("Invalid delete temp file: %s", basename($filepath)),
            context: [
                'filepath' => $filepath,
            ],
            code: 400,
            solutions: [
                'Check if temp file exists.',
                'Check filepath and ensure file exists',
                'Check file permission: chmod +r ' . basename($filepath)
            ]
        );
    }

    public static function invalidPut(string $filepath): self
    {
        return new self(
            message: sprintf("Invalid put temp file: %s", basename($filepath)),
            context: [
                'filepath' => $filepath,
            ],
            code: 400,
            solutions: [
                'Check if temp file exists.',
                'Check filepath and ensure file exists',
                'Check file permission: chmod +r ' . basename($filepath)
            ]
        );
    }
}
