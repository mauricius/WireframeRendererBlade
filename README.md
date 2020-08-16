## WireframeRendererBlade

Blade renderer for the ProcessWire [Wireframe](https://wireframe-framework.com/) output framework.

---

This module is an optional renderer add-on for the Wireframe output framework, adding support for the [Laravel Blade](https://laravel.com/docs/5.8/blade) templating engine.

## Basic usage

First of all, you need to install both Wireframe and WireframeRenderBlade.

Then you have to install the dependencies of WireframeRenderBlade by running

```
composer install
```

in the module folder.

Finally you can set up Wireframe (as instructed at https://wireframe-framework.com/getting-started/). Once that's done, you can open the bootstrap file (`wireframe.php`) and instruct Wireframe to use the Blade renderer:

```php
// during Wireframe init (this is the preferred way):
$wireframe->init([
    'renderer' => ['WireframeRendererBlade', [
        // optional settings array
    ]],
]);

// ... or after init (this incurs a slight overhead):
$wireframe->setRenderer('WireframeRendererBlade', [
    // optional settings array
]);
```

### Settings

You can provide your values for the `views` and `cache` paths. Default values are outlined below:

```php
[
    'views' => '/site/templates/views',
    'cache' => '/site/assets/cache/WireframeRendererBlade'
]
```

## Blade templates

Once you've told Wireframe to use the Blade renderer, by default it will attempt to render all your views, layouts, partials and components using Blade. File extension for Blade files is `.blade.php`.

Note that if a Blade file can't be found, Wireframe will automatically fall back to native (`.php`) file. This is intended to ease migrating from PHP to Blade, and also makes it possible for Blade and PHP view files to co-exist.

### Layouts

Blade allows you to extend a parent layout using the Blade `@extends` directive, to specify which layout the child view should "inherit". By default all layouts are referenced from the `views` folder

```
@extends('layout')
```

```
.
|-- views
|   |-- layout.blade.php
|   └-- child.blade.php
```

Otherwise you can keep the Wireframe concept of layout and use a Blade file from the `layouts` folder, by prefixing the layout with the `layout::` namespace

```
@extends('layout::layout')
```

```
.
|-- layout
|   └-- layout.blade.php
|-- views
|   └-- child.blade.php
```

### Includes (partials)

The same concept is valid for sub-views. You can include a Blade file from another view using the `@include` directive. By defaut it will pick up partials from the `views` folder. If you prefer to use a file from the Wireframe `partials` directory you can prefix the partial name with the `partial::` namespace.

### Extending Blade

If you want to extend Blade (e.g. add a new directive), you can access the Blade Environment by hooking into `WireframeRendererBlade::initBlade`:

#### Adding a Custom Directive

```php
// site/ready.php
$wire->addHookAfter('WireframeRendererBlade::initBlade', function(HookEvent $event) {
    $event->return->directive('hello', function ($expression) {
        return "<?php echo 'Hello ' . {$expression}; ?>";
    });
});

```

```
@hello('World')
```

#### Adding a Custom If Directive

```php
// site/ready.php
$wire->addHookAfter('WireframeRendererBlade::initBlade', function(HookEvent $event) use ($user) {
    $event->return->if('superuser', function () use ($user) {
        return $user->isLoggedIn() and $user->isSuperuser();
    });
});

```

```
@superuser
    <h2>Hello</h2>
@else
    <p>You don't have access here</p>
@endsuperuser
```

#### Adding a directive to cache partial HTML with [Markup Cache Module](https://modules.processwire.com/modules/markup-cache/)

```php
// site/ready.php
$wire->addHookAfter('WireframeRendererBlade::initBlade', function(HookEvent $event) use ($wire) {
     $event->return->directive('cache', function ($expression) use ($wire) {
        $args = array_map(function ($item) {
            return trim($item);
        }, explode(',', $expression));

        $key = substr($args[0], 1, -1);
        $duration = $args[1] ?? (24 * 60 * 60); // 24 hours

        return implode('', [
            "<?php ob_start(); ?>",
            '<?php if (! $__partial = $modules->get("MarkupCache")->get("' . $key . '",' . $duration . ')) : ?>',
        ]);
    });

    $event->return->directive('endcache', function () {
        return implode('', [
            '<?php endif; ?>',
            '<?php if (! $__partial): ?>',
            '<?php $__partial = ob_get_clean(); ?>',
            '<?php $modules->get("MarkupCache")->save($__partial); ?>',
            '<?php else : ?>',
            '<?php ob_end_clean(); ?>',
            '<?php endif; ?>',
            '<?php echo $__partial; ?>',
        ]);
    });
});

```

```
@cache('my-cache-key', 3600)
    {{-- heavy stuff here --}}
@endcache
```

You can also access the [underlying Blade class](https://github.com/jenssegers/blade) from the `wireframe.php` file:

```php
$blade = $wireframe->view->getRenderer()->getBladeInstance();
```

### Localization

[ProcessWire translation functions](https://processwire.com/docs/multi-language-support/code-i18n/) are provided as helpers so you can use `__()` and `_n()` directly in the templates, keeping ProcessWire ability to detect strings that need translation. However you will have to manually override the [textdomain](https://processwire.com/docs/multi-language-support/code-i18n/#using-textdomains) to use the path of the uncompile Blade file, otherwise ProcessWire will not be able to match your translations.

However this is easier as it sounds. Just add this code to your `wireframe.php` file

```php
/**
 * Convert template path into valid textdomain
 * e.g. from /site/templates/views/mypage.blade.php
 * into site--templates--views--mypage-blade-php
 * @param  string $path
 * @return string
 */
function getViewTextDomain($path) {
    $config = wire('config');

    $input = str_replace('.', '-', str_replace($config->paths->root, '', $path));

    $re = '/\/+/m';

    return preg_replace($re, '--', $input);
}

$wireframe->view->getRenderer()->getBladeInstance()->composer('*', function($view)
{
    $view->with('textdomain', Utils::getViewTextDomain($view->getPath()));
});
```

Finally you can use the `$textdomain` variable wherever you need to translate strings

```
{{ __("Translate me", $textdomain) }}
```

### Gotchas

Some Blade directives don't work in the ProcessWire context. A few examples are `@inject`, `@can` and `@cannot`.

## License

[MPL](LICENSE)
