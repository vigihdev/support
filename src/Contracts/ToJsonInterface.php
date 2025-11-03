<?php

declare(strict_types=1);

namespace Vigihdev\Support\Contracts;

interface ToJsonInterface
{
    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson(int $options = 0): string;
}