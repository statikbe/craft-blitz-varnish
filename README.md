# Blitz Varnish Purger for Craft CMS

The Varnish Cache Purger allows the [Blitz](https://putyourlightson.com/plugins/blitz) plugin for [Craft CMS](https://craftcms.com/) to intelligently purge cached pages on connected Varnish servers.

## Usage

Install the plugin using composer and then install it.

```
composer require statikbe/craft-blitz-varnish
php craft plugin/install blitz-varnish
```

You can then select the purger and settings either in the control panel or in `config/blitz.php`.

```php
// The purger type to use.
'cachePurgerType' => 'statikbe\blitzvarnish\VarnishCachePurger',

```

## Using Varnish Cache with Craft CMS
How to configure Varnish Cache to work with Craft CMS and your specific project is beyond the scope of this plugin. 
You can find a basic example configuration [here](https://github.com/statikbe/craft-blitz-varnish/blob/main/example.vcl).

Created by [statik.be](https://www.statik.be).
