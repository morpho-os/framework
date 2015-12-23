# PHP 7 framework: Morpho

Morpho is **PHP 7** framework to build PHP applications: Web-sites, Web-applications, Web-IDEs, Command-line applications.


## Installation for development

1) Install packages:
```
git clone https://github.com/morpho-os/framework.git
cd framework

# Install Composer and packages for backend:
curl -s --output /usr/local/bin/composer https://getcomposer.org/composer.phar
chmod +x /usr/local/bin/composer
composer install

# Install Bower and packages for frontend:
npm install -g bower
cd public
bower install
```

2) Create virtual host for the 'public' directory, for example 'test'.

3) Open URI http://test in the browser, installer should appear.


## Current build status

[![Build Status](https://travis-ci.org/morpho-os/framework.svg?branch=master)](https://travis-ci.org/morpho-os/framework)


## License

License is Apache-2.0.


## Who is behind Morpho and commercial support

Behind the Morpho framework is the [NeuroJazz Studio](http://neurojazz.com). We provide commercial support for the Morpho framework.

