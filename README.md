# Laravel Model Encryptor

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![PHP Version](https://img.shields.io/packagist/php-v/pmingram/laravel-model-encryptor.svg?style=flat-square)](https://php.net)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/pmingram/laravel-model-encryptor.svg?style=flat-square)](https://packagist.org/packages/juststeveking/laravel-api-toolkit)

A model trait for flexible encryption and decryption of data

## Installation

You can install the package via composer:

```bash
composer require pmingram/laravel-model-encryptor
```

## Usage and Configuration

This package is a trait that can be added to any Laravel model you wish to apply encryption to:

```bash
use PmIngram\Laravel\ModelEncryptor\HasEncryption;

class ModelName extends Model
{
    use HasEncryption;
}
```

By default, the trait will apply encryption to any record on creation - but to actually encrypt data, you need to configure some properties within your model:

| Property | Type | Description | Default Value |
|---|---|---|---|
| $encryptOnCreate | Boolean | Enable or disable encryption on model creation. If set to `false`, the model can be encrypted later by invoking `$model->encrypt(true)`. | `true` |
| $encryptionKey | String | A random string to act as a base encryption key for the model in conjunction with the application key set by Laravel. If left blank, the Laravel application key will be used alone. | Empty string |
| $encryptionSaltColumn | String | The data in the column defined here will be appended to the `$encryptionKey` to create a per-row encryption key. This should **not** be a column in the `$encryptionColumnKeys` array, as this could lead to data loss. | Empty string |
| $encryptionColumnKeys | Array | List of columns to be included in the encryption and decryption processes. These columns should be defined as `LONGTEXT` datatypes (or the equivalent type in your database engine.) | Empty array |

There is no specific scope requirement for these properties, but it is recommended to use a `protected` scope.

## Encryption and Decryption

Models can be encrypted and decrypted easily with a simple method call:

```bash
$model->encrypt();
$model->decrypt();
```

This will encrypt or decrypt the data within the current model instance, but will not persist the change to the database.
This is particularly useful in the event you wish to decrypt the data for presentation (for example, in a view or in a 
resource for an API endpoint) but you want to keep that data encrypted in the database.

To persist to the database, simply pass `true` as an optional argument within the method:

```bash
$model->encrypt(true);
$model->decrypt(true);
```

## Example Configuration

### Model
```bash
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PmIngram\Laravel\ModelEncryptor\HasEncryption;

class ExampleModel extends Model
{
    use HasEncryption, HasFactory;

    protected $encryptOnCreate = true;
    protected $encryptionKey = '';
    protected $encryptionSaltColumn = 'column_b';
    protected $encryptionColumnKeys = [
        'column_c', 'column_e',
    ];

    protected $fillable = [
        'column_a', 'column_b', 'column_c', 'column_d', 'column_e',
    ];

    public function __construct(array $attributes = [])
    {
        $this->encryptionKey = config('encryptionkeys.model.example');

        parent::__construct($attributes);
    }
}
```

### Laravel Configuration File - "encryptionkeys.php"

```bash
<?php

return [
    'models' => [
        'example' => env('ENCKEY_MODEL_EXAMPLE', null),
    ]
];

```

### Environment Variable (.env)

```bash
ENCKEY_MODEL_EXAMPLE=somerandomstring
```

## Recommendation on Encryption Keys and Security

While it is entirely possible to store the model-level encryption key within the model itself, as a string in the `$encryptionKey`
property, it is **strongly advised** to abstract the string out to a configuration file as per the example above, then use the `.env` file to
set the strings.

It is both bad practice and a security risk to store encryption keys and passwords within a codebase,
especially when that codebase is persisted to a VCS such as Git or SVN.

## Important Note

The Laravel application key is used with this trait. If the application key is changed, **any encrypted data will no longer be readable**.
Of course this is the case with any encryption routines deployed, but should be considered if you need to change your application's key.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.