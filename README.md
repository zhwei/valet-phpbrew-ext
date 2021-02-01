# PHPBrew extension for Laravel Valet

[![Latest Stable Version](https://poser.pugx.org/zhwei/valet-phpbrew-ext/version.png)](https://packagist.org/packages/zhwei/valet-phpbrew-ext)
[![Total Downloads](https://poser.pugx.org/zhwei/valet-phpbrew-ext/d/total.png)](https://packagist.org/packages/zhwei/valet-phpbrew-ext)

## Usage

1. Composer install

```bash
composer global require zhwei/valet-phpbrew-ext
```

2. Register extension file to Valet

```bash
ln -s $HOME/.composer/vendor/zhwei/valet-phpbrew-ext/phpbrew-ext.php $HOME/.config/valet/Extensions/phpbrew-ext.php
```


3. Run

```bash
# create site (PHP_VERSION and SITE_NAME is optional)
sudo valet phpbrew:link [PHP_VERSION] [SITE_NAME]

# list sites created by phpbrew:link
sudo valet phpbrew:links

# unlink site (SITE_NAME is optional)
sudo valet phpbrew:unlink [SITE_NAME]
```


## Uninstall

```bash
# remove extension register file
rm $HOME/.config/valet/Extensions/phpbrew-ext.php

# remove global dependency
composer global remove zhwei/valet-phpbrew-ext
```
