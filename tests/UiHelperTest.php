<?php

declare(strict_types=1);

namespace Vigihdev\Support\Tests;

use Vigihdev\Support\UiHelper;
use PHPUnit\Framework\TestCase;

final class UiHelperTest extends TestCase
{
    public function testFor(): void
    {
        $UiHelper = UiHelper::for('test');
        self::assertInstanceOf(UiHelper::class, $UiHelper);

        // Use reflection to access the private name property
        $reflection = new \ReflectionClass($UiHelper);
        $property = $reflection->getProperty('name');
        $property->setAccessible(true);
        self::assertEquals('test', $property->getValue($UiHelper));
    }

    public function testOnclick(): void
    {
        $UiHelper = new UiHelper('test');

        // Basic URL
        $result = $UiHelper->onclick('https://example.com');
        self::assertEquals('location.href="https:\/\/example.com"', $result);

        // URL with special characters (ampersand gets encoded)
        $result = $UiHelper->onclick('https://example.com?param=value&other=test');
        self::assertEquals('location.href="https:\/\/example.com?param=value\u0026other=test"', $result);

        // Empty URL
        $result = $UiHelper->onclick('');
        self::assertEquals('location.href=""', $result);
    }

    public function testWindowOpen(): void
    {
        $UiHelper = new UiHelper('test');

        // Basic usage with default target
        $result = $UiHelper->windowOpen('https://example.com');
        self::assertEquals('window.open("https:\/\/example.com","_blank");', $result);

        // Custom target
        $result = $UiHelper->windowOpen('https://example.com', '_self');
        self::assertEquals('window.open("https:\/\/example.com","_self");', $result);

        // URL and target with special characters
        $result = $UiHelper->windowOpen('https://example.com?param="value"', '_blank');
        self::assertEquals('window.open("https:\/\/example.com?param=\u0022value\u0022","_blank");', $result);
    }

    public function testEncodeForJavaScript(): void
    {
        $UiHelper = new UiHelper('test');

        // Basic string
        $result = $UiHelper->encodeForJavaScript('hello world');
        self::assertEquals('"hello world"', $result);

        // String with special characters that should be encoded
        $result = $UiHelper->encodeForJavaScript('<script>alert("xss")</script>');
        self::assertEquals('"\\u003Cscript\\u003Ealert(\\u0022xss\\u0022)\\u003C\\/script\\u003E"', $result);

        // Empty string
        $result = $UiHelper->encodeForJavaScript('');
        self::assertEquals('""', $result);

        // String with quotes and apostrophes
        $result = $UiHelper->encodeForJavaScript('He said "Hello" and \'Goodbye\'');
        self::assertEquals('"He said \\u0022Hello\\u0022 and \\u0027Goodbye\\u0027"', $result);
    }

    public function testBuildClassString(): void
    {
        $UiHelper = new UiHelper('test');

        // Basic array of classes
        $result = $UiHelper->buildClassString(['class1', 'class2', 'class3']);
        self::assertEquals('class1 class2 class3', $result);

        // Array with empty strings and null values filtered out
        $result = $UiHelper->buildClassString(['class1', '', 'class2', null, 'class3']);
        self::assertEquals('class1 class2 class3', $result);

        // Array with boolean values (should be filtered out)
        $result = $UiHelper->buildClassString(['class1', true, 'class2', false, 'class3']);
        self::assertEquals('class1 class2 class3', $result);

        // Empty array
        $result = $UiHelper->buildClassString([]);
        self::assertEquals('', $result);

        // Array with numeric values (should be filtered out since they're not strings)
        $result = $UiHelper->buildClassString(['class1', 123, 'class2']);
        self::assertEquals('class1 class2', $result);
    }

    public function testMergeOptions(): void
    {
        $UiHelper = new UiHelper('test');

        // Basic merge with defaults
        $defaults = ['option1' => 'default1', 'option2' => 'default2'];
        $options = ['option1' => 'custom1', 'option3' => 'custom3'];
        $result = $UiHelper->mergeOptions($options, $defaults);
        $expected = ['option1' => 'custom1', 'option2' => 'default2', 'option3' => 'custom3'];
        self::assertEquals($expected, $result);

        // Options override defaults
        $defaults = ['color' => 'blue', 'size' => 'medium'];
        $options = ['color' => 'red'];
        $result = $UiHelper->mergeOptions($options, $defaults);
        $expected = ['color' => 'red', 'size' => 'medium'];
        self::assertEquals($expected, $result);

        // Empty options
        $defaults = ['option1' => 'default1'];
        $options = [];
        $result = $UiHelper->mergeOptions($options, $defaults);
        $expected = ['option1' => 'default1'];
        self::assertEquals($expected, $result);

        // Empty defaults
        $defaults = [];
        $options = ['option1' => 'custom1'];
        $result = $UiHelper->mergeOptions($options, $defaults);
        $expected = ['option1' => 'custom1'];
        self::assertEquals($expected, $result);
    }

    public function testGetCardName(): void
    {
        $UiHelper = new UiHelper('component');

        // Basic card name
        $result = $UiHelper->getCardName();
        self::assertEquals('component-card', $result);

        // Card name with specific name
        $result = $UiHelper->getCardName('header');
        self::assertEquals('component-card__header', $result);

        // Card name with complex name
        $result = $UiHelper->getCardName('main-content');
        self::assertEquals('component-card__main-content', $result);

        // Card name with null (should return base card name)
        $result = $UiHelper->getCardName(null);
        self::assertEquals('component-card', $result);
    }

    public function testGetBlockName(): void
    {
        $UiHelper = new UiHelper('component');

        // Basic block name
        $result = $UiHelper->getBlockName('button');
        self::assertEquals('component__button', $result);

        // Block name with complex name
        $result = $UiHelper->getBlockName('navigation-menu');
        self::assertEquals('component__navigation-menu', $result);

        // Block name with numbers
        $result = $UiHelper->getBlockName('section1');
        self::assertEquals('component__section1', $result);
    }

    public function testGetModifierClass(): void
    {
        $UiHelper = new UiHelper('component');

        // Basic modifier class
        $result = $UiHelper->getModifierClass('button', 'primary');
        self::assertEquals('component__button--primary', $result);

        // Modifier with complex names
        $result = $UiHelper->getModifierClass('nav-item', 'active-large');
        self::assertEquals('component__nav-item--active-large', $result);

        // Modifier with special characters
        $result = $UiHelper->getModifierClass('form-field', 'error--focused');
        self::assertEquals('component__form-field--error--focused', $result);
    }

    public function testTransformWithName(): void
    {
        $UiHelper = new UiHelper('component');

        // Basic transformation
        $result = $UiHelper->transformWithName('id');
        self::assertEquals('component-id', $result);

        // Transformation with complex param
        $result = $UiHelper->transformWithName('data-value');
        self::assertEquals('component-data-value', $result);

        // Transformation with number
        $result = $UiHelper->transformWithName('123');
        self::assertEquals('component-123', $result);

        // Empty param
        $result = $UiHelper->transformWithName('');
        self::assertEquals('component-', $result);
    }
}
