<?php

declare(strict_types=1);

namespace Vigihdev\Support\Exceptions;

use Throwable;
use Vigihdev\Support\Exceptions\SupportExceptionInterface;

class SupportException extends \Exception implements SupportExceptionInterface
{

    protected array $context = [];

    protected array $solutions = [];

    public static function handleThrowable(
        Throwable|SupportExceptionInterface $e,
        array $context = [],
        array $solutions = []
    ): self {
        $context = method_exists($e, 'getContext') && is_array($e->getContext()) ? $e->getContext() : $context;
        $solutions = method_exists($e, 'getSolutions') && is_array($e->getSolutions()) ? $e->getSolutions() : $solutions;

        return new self(
            message: $e->getMessage(),
            code: $e->getCode(),
            previous: $e,
            context: $context,
            solutions: $solutions,
        );
    }

    public function __construct(
        string $message,
        int $code = 0,
        \Throwable $previous = null,
        array $context = [],
        array $solutions = []
    ) {
        $this->context = $context;
        $this->solutions = $solutions;
        parent::__construct($message, $code, $previous);
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getSolutions(): array
    {
        return $this->solutions;
    }

    public function getFormattedMessage(): string
    {
        $message = $this->getMessage();

        if (!empty($this->context)) {
            $contextStr = json_encode($this->context, JSON_UNESCAPED_SLASHES);
            $message .= " (context: {$contextStr})";
        }
        return $message;
    }
}
