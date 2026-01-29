<?php

declare(strict_types=1);

namespace Vigihdev\Support;

final class UiHelper
{

    /**
     * Create new Renderer instance
     * 
     * @param string $name
     * @return self
     */
    public static function for(string $name): self
    {
        return new self($name);
    }

    public function __construct(
        private readonly string $name,
    ) {}

    /**
     * Generate onclick event for URL
     * 
     * @param string $url
     * @return string
     */
    public function onclick(string $url): string
    {
        $encodedUrl = $this->encodeForJavaScript($url);
        return "location.href={$encodedUrl}";
    }

    /**
     * Generate window.open event for URL
     * 
     * @param string $url
     * @param string $target
     * @return string
     */
    public function windowOpen(string $url, string $target = '_blank'): string
    {
        $encodedUrl = $this->encodeForJavaScript($url);
        $encodedTarget = $this->encodeForJavaScript($target);
        return "window.open({$encodedUrl},{$encodedTarget});";
    }

    /**
     * Encode string for JavaScript
     * 
     * @param string $value
     * @return string
     */
    public function encodeForJavaScript(string $value): string
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }

    /**
     * Build CSS class string from array
     * 
     * @param array $classes
     * @return string
     */
    public function buildClassString(array $classes): string
    {
        return implode(' ', array_filter($classes, fn($value) => is_string($value) && strlen($value) > 0));
    }

    /**
     * Merge options with defaults
     * 
     * @param array $options
     * @param array $defaults
     * @return array
     */
    public function mergeOptions(array $options, array $defaults = []): array
    {
        return array_merge($defaults, $options);
    }

    /**
     * Generate BEM card class
     * 
     * @param string $name
     * @return string
     */
    public function getCardName(string $name = null): string
    {
        $card = $this->name . '-card';
        return $name ? "{$card}__{$name}" : $card;
    }

    /**
     * Generate BEM block class
     * 
     * @param string $block
     * @return string
     */
    public function getBlockName(string $block): string
    {
        $name = $this->name;
        return "{$name}__{$block}";
    }

    /**
     * Generate BEM modifier class
     * 
     * @param string $block
     * @param string $modifier
     * @return string
     */
    public function getModifierClass(string $block, string $modifier): string
    {
        return $this->getBlockName($block) . '--' . $modifier;
    }

    /**
     * Transform param with renderer name
     * 
     * @param string $param
     * @return string
     */
    public function transformWithName(string $param): string
    {
        return $this->name . "-{$param}";
    }
}
