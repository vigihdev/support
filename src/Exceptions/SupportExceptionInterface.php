<?php

declare(strict_types=1);

namespace Vigihdev\Support\Exceptions;

use Throwable;

interface SupportExceptionInterface extends Throwable
{
    public function getContext(): array;

    public function getSolutions(): array;

    public function getFormattedMessage(): string;
}
