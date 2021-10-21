
# Morph Cascade Delete
[![MIT License](https://img.shields.io/apm/l/atomic-design-ui.svg?)](https://github.com/tterb/atomic-design-ui/blob/master/LICENSEs)

A simple laravel package to enable cascade deleting on polymorphic relations.


## Installation

Install with composer

```bash
  composer require moataz-hajres/morph-cascade-delete
```

## Compatibility

Tested on Laravel 8 with php 8, but it should work fine on laravel 6,7 with php ^7|^8.

## Usage

1- Use the trait within models that has child morph relations.

2- Make sure to define the morphOne/morphMany relation methods as `public` & `final`.

```php
....

class User extends Model {

    use MorphCascadeDelete;

    final public function images() {
        return $this->morphMany(Image::class, 'imageable');
    }

}

...
```
That's it, you're all set!

  
## License

[MIT](https://choosealicense.com/licenses/mit/)
