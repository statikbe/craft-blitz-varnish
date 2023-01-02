# Blitz Varnish Purger for Craft CMS

The Varnish Cache Purger allows the [Blitz](https://putyourlightson.com/plugins/blitz) plugin for [Craft CMS](https://craftcms.com/) to intelligently purge cached pages on connected Varnish servers.

## Usage

Install the purger using composer.

```
composer require statikbe/craft-blitz-varnish
```

Then add the class to the `cachePurgerTypes` config setting in `config/blitz.php`.

```php
// The purger type classes to add to the plugin’s default purger types.
'cachePurgerTypes' => [
    'statikbe\blitz\drivers\purgers\CloudflarePurger',
    'statikbe\blitzvarnish\VarnishCachePurger',
],
```

You can then select the purger and settings either in the control panel or in `config/blitz.php`.

```php
// The purger type to use.
'cachePurgerType' => 'statikbe\blitzvarnish\VarnishCachePurger',

// The purger settings.
'cachePurgerSettings' => [
 // TODO
],
```

## Documentation


Created by [statik.be](https://www.statik.be).
