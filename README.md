# PHPBrew extension for Laravel Valet

## Usage

1. Composer install

```php
composer global require zhwei/valet-phpbrew-ext
```

2. Register extension file to Valet

```php
ln -s $HOME/.composer/vendor/zhwei/valet-phpbrew-ext/phpbrew-ext.php $HOME/.config/valet/Extensions/phpbrew-ext.php
```


3. Run

```bash
# create site
sudo valet phpbrew:link PHPVERSION [SITE_NAME]

# list sites create by phpbrew:link
sudo valet phpbrew:links

# unlink site
sudo valet phpbrew:unlink [SITE_NAME]
```


## Uninstall

```bash
# remove extension file
rm $HOME/.config/valet/Extensions/phpbrew-ext.php

# remove require
composer global remove zhwei/valet-phpbrew-ext
```
