# VigihDev Support Library

[![Tests](https://img.shields.io/badge/tests-97%20passed-brightgreen)](https://github.com/vigihdev/support)
[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue)](https://php.net)

A robust set of utility and helper classes for PHP development, including string manipulation, array helpers, file operations, and collections.

## Installation

```bash
composer require vigihdev/support
```

## Requirements

- PHP ^8.1
- Symfony String ^6.4

## Usage

### Text Helper

```php
use Vigihdev\Support\Text;

// Case conversions
Text::camelCase('hello_world');        // 'helloWorld'
Text::pascalCase('hello_world');       // 'HelloWorld'
Text::snakeCase('HelloWorld');         // 'hello_world'
Text::kebabCase('hello_world');        // 'hello-world'
Text::toTitleCase('hello world');      // 'Hello World'

// String utilities
Text::slugify('Hello World!');         // 'hello-world'
Text::truncate('Long text...', 10);    // 'Long text...'
Text::contains('Hello World', 'World'); // true
Text::random(10);                      // Random 10-char string
Text::toReadableLabel('firstName');    // 'First Name'
```

### Array Helper

```php
use Vigihdev\Support\Arr;

$array = [
    'name' => 'John Doe',
    'address' => [
        'street' => '123 Main St',
        'city' => 'Anytown'
    ],
    'emails' => ['john@example.com', 'work@example.com']
];

// Get values with dot notation
Arr::get($array, 'name');              // 'John Doe'
Arr::get($array, 'address.street');    // '123 Main St'
Arr::get($array, 'missing', 'default'); // 'default'

// Check existence
Arr::has($array, 'name');              // true
Arr::has($array, 'address.city');      // true
Arr::exists($array, 'name');           // true

// Array manipulation
Arr::only($array, ['name', 'emails']); // Only specified keys
Arr::except($array, ['address']);      // All except specified keys
Arr::pluck($users, 'name');            // Extract column values
Arr::first($array);                    // First element
Arr::last($array);                     // Last element
Arr::flatten($nested);                 // Flatten nested arrays
Arr::dot($array);                      // Convert to dot notation
```

### Collection

```php
use Vigihdev\Support\Collection;

$collection = new Collection([1, 2, 3, 4, 5]);

// Basic operations
$collection->count();                   // 5
$collection->isEmpty();                 // false
$collection->toArray();                 // [1, 2, 3, 4, 5]

// Functional methods
$collection->filter(fn($x) => $x > 3);  // [4, 5]
$collection->map(fn($x) => $x * 2);     // [2, 4, 6, 8, 10]
$collection->reduce(fn($carry, $x) => $carry + $x, 0); // 15

// Access methods
$collection->first();                   // 1
$collection->last();                    // 5
$collection->get(2);                    // 3
$collection->has(1);                    // true

// Array methods
$collection->keys();                    // [0, 1, 2, 3, 4]
$collection->values();                  // [1, 2, 3, 4, 5]
$collection->slice(1, 3);               // [2, 3, 4]
$collection->chunk(2);                  // [[1, 2], [3, 4], [5]]

// Advanced operations
$users = new Collection([
    ['name' => 'John', 'age' => 30],
    ['name' => 'Jane', 'age' => 25]
]);
$users->pluck('name');                  // ['John', 'Jane']
$users->groupBy('age');                 // Group by age
$users->sortBy('name');                 // Sort by name

// JSON conversion
$collection->toJson();                  // JSON string
```

### File Helper

```php
use Vigihdev\Support\File;

// Read/Write operations
File::get('path/to/file.txt');          // Read file content
File::put('path/to/file.txt', 'content'); // Write to file
File::append('path/to/file.txt', 'more'); // Append to file

// File information
File::exists('path/to/file.txt');       // true/false
File::missing('path/to/file.txt');      // true/false
File::size('path/to/file.txt');         // File size in bytes
File::extension('file.txt');            // 'txt'
File::name('path/to/file.txt');         // 'file.txt'
File::basename('path/to/file.txt');     // 'file'
File::dirname('path/to/file.txt');      // 'path/to'
File::type('path/to/file.txt');         // 'file'
File::mimeType('image.jpg');            // 'image/jpeg'
File::lastModified('file.txt');         // Unix timestamp

// File operations
File::delete('path/to/file.txt');       // Delete file
File::move('old/path.txt', 'new/path.txt'); // Move file
File::copy('source.txt', 'dest.txt');   // Copy file

// Directory operations
File::makeDirectory('path/to/dir');     // Create directory
File::deleteDirectory('path/to/dir');   // Delete directory
File::cleanDirectory('path/to/dir');    // Empty directory
File::files('path/to/dir');             // List files
File::directories('path/to/dir');       // List directories
File::allFiles('path/to/dir');          // Recursive file list
File::allDirectories('path/to/dir');    // Recursive directory list
```

## Testing

```bash
composer test
```

## Development Server

```bash
composer run server
```

## License

MIT License - see [LICENCE](LICENCE) file for details.

## Author

**Vigih Dev**  
Email: vigihdev@gmail.com
