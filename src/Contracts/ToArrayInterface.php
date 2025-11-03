<?php

declare(strict_types=1);

namespace Vigihdev\Support\Contracts;

interface ToArrayInterface
{
    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array;
}