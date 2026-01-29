<?php

declare(strict_types=1);

namespace Vigihdev\Support\Tests;

use Vigihdev\Support\Renderer;
use PHPUnit\Framework\TestCase;

final class RendererTest extends TestCase
{
    public function testFor(): void
    {
        $renderer = Renderer::for('test');
        self::assertInstanceOf(Renderer::class, $renderer);

        // Use reflection to access the private name property
        $reflection = new \ReflectionClass($renderer);
        $property = $reflection->getProperty('name');
        $property->setAccessible(true);
        self::assertEquals('test', $property->getValue($renderer));
    }

    public function testOnclick(): void
    {
        $renderer = new Renderer('test');

        // Basic URL
        $result = $renderer->onclick('https://example.com');
        self::assertEquals('location.href="https:\/\/example.com"', $result);

        // URL with special characters (ampersand gets encoded)
        $result = $renderer->onclick('https://example.com?param=value&other=test');
        self::assertEquals('location.href="https:\/\/example.com?param=value\u0026other=test"', $result);

        // Empty URL
        $result = $renderer->onclick('');
        self::assertEquals('location.href=""', $result);
    }

    public function testWindowOpen(): void
    {
        $renderer = new Renderer('test');

        // Basic usage with default target
        $result = $renderer->windowOpen('https://example.com');
        self::assertEquals('window.open("https:\/\/example.com","_blank");', $result);

        // Custom target
        $result = $renderer->windowOpen('https://example.com', '_self');
        self::assertEquals('window.open("https:\/\/example.com","_self");', $result);

        // URL and target with special characters
        $result = $renderer->windowOpen('https://example.com?param="value"', '_blank');
        self::assertEquals('window.open("https:\/\/example.com?param=\u0022value\u0022","_blank");', $result);
    }

    public function testEncodeForJavaScript(): void
    {
        $renderer = new Renderer('test');

        // Basic string
        $result = $renderer->encodeForJavaScript('hello world');
        self::assertEquals('"hello world"', $result);

        // String with special characters that should be encoded
        $result = $renderer->encodeForJavaScript('<script>alert("xss")</script>');
        self::assertEquals('"\\u003Cscript\\u003Ealert(\\u0022xss\\u0022)\\u003C\\/script\\u003E"', $result);

        // Empty string
        $result = $renderer->encodeForJavaScript('');
        self::assertEquals('""', $result);

        // String with quotes and apostrophes
        $result = $renderer->encodeForJavaScript('He said "Hello" and \'Goodbye\'');
        self::assertEquals('"He said \\u0022Hello\\u0022 and \\u0027Goodbye\\u0027"', $result);
    }

    public function testBuildClassString(): void
    {
        $renderer = new Renderer('test');

        // Basic array of classes
        $result = $renderer->buildClassString(['class1', 'class2', 'class3']);
        self::assertEquals('class1 class2 class3', $result);

        // Array with empty strings and null values filtered out
        $result = $renderer->buildClassString(['class1', '', 'class2', null, 'class3']);
        self::assertEquals('class1 class2 class3', $result);

        // Array with boolean values (should be filtered out)
        $result = $renderer->buildClassString(['class1', true, 'class2', false, 'class3']);
        self::assertEquals('class1 class2 class3', $result);

        // Empty array
        $result = $renderer->buildClassString([]);
        self::assertEquals('', $result);

        // Array with numeric values (should be filtered out since they're not strings)
        $result = $renderer->buildClassString(['class1', 123, 'class2']);
        self::assertEquals('class1 class2', $result);
    }

    public function testMergeOptions(): void
    {
        $renderer = new Renderer('test');

        // Basic merge with defaults
        $defaults = ['option1' => 'default1', 'option2' => 'default2'];
        $options = ['option1' => 'custom1', 'option3' => 'custom3'];
        $result = $renderer->mergeOptions($options, $defaults);
        $expected = ['option1' => 'custom1', 'option2' => 'default2', 'option3' => 'custom3'];
        self::assertEquals($expected, $result);

        // Options override defaults
        $defaults = ['color' => 'blue', 'size' => 'medium'];
        $options = ['color' => 'red'];
        $result = $renderer->mergeOptions($options, $defaults);
        $expected = ['color' => 'red', 'size' => 'medium'];
        self::assertEquals($expected, $result);

        // Empty options
        $defaults = ['option1' => 'default1'];
        $options = [];
        $result = $renderer->mergeOptions($options, $defaults);
        $expected = ['option1' => 'default1'];
        self::assertEquals($expected, $result);

        // Empty defaults
        $defaults = [];
        $options = ['option1' => 'custom1'];
        $result = $renderer->mergeOptions($options, $defaults);
        $expected = ['option1' => 'custom1'];
        self::assertEquals($expected, $result);
    }

    public function testGetCardName(): void
    {
        $renderer = new Renderer('component');

        // Basic card name
        $result = $renderer->getCardName();
        self::assertEquals('component-card', $result);

        // Card name with specific name
        $result = $renderer->getCardName('header');
        self::assertEquals('component-card__header', $result);

        // Card name with complex name
        $result = $renderer->getCardName('main-content');
        self::assertEquals('component-card__main-content', $result);

        // Card name with null (should return base card name)
        $result = $renderer->getCardName(null);
        self::assertEquals('component-card', $result);
    }

    public function testGetBlockName(): void
    {
        $renderer = new Renderer('component');

        // Basic block name
        $result = $renderer->getBlockName('button');
        self::assertEquals('component__button', $result);

        // Block name with complex name
        $result = $renderer->getBlockName('navigation-menu');
        self::assertEquals('component__navigation-menu', $result);

        // Block name with numbers
        $result = $renderer->getBlockName('section1');
        self::assertEquals('component__section1', $result);
    }

    public function testGetModifierClass(): void
    {
        $renderer = new Renderer('component');

        // Basic modifier class
        $result = $renderer->getModifierClass('button', 'primary');
        self::assertEquals('component__button--primary', $result);

        // Modifier with complex names
        $result = $renderer->getModifierClass('nav-item', 'active-large');
        self::assertEquals('component__nav-item--active-large', $result);

        // Modifier with special characters
        $result = $renderer->getModifierClass('form-field', 'error--focused');
        self::assertEquals('component__form-field--error--focused', $result);
    }

    public function testTransformWithName(): void
    {
        $renderer = new Renderer('component');

        // Basic transformation
        $result = $renderer->transformWithName('id');
        self::assertEquals('component-id', $result);

        // Transformation with complex param
        $result = $renderer->transformWithName('data-value');
        self::assertEquals('component-data-value', $result);

        // Transformation with number
        $result = $renderer->transformWithName('123');
        self::assertEquals('component-123', $result);

        // Empty param
        $result = $renderer->transformWithName('');
        self::assertEquals('component-', $result);
    }
}