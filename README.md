# PHP 7 framework: Morpho

Morpho is **PHP 7** framework to build PHP applications: Web-sites, Web-applications, Web-IDEs, Command-line applications.


## Installation for development

### Overview

1) Install tools: Composer and Node.js

2) Install packages


### Step 1. Install Composer and Node.js

#### Linux

1) Install Composer:
```
curl -s --output /usr/local/bin/composer https://getcomposer.org/composer.phar
chmod +x /usr/local/bin/composer
```

2) Install Node.js:
```
mkdir nodejs
cd nodejs
releaseCodeName=$(curl -sk https://nodejs.org/download/release/ | grep -E 'latest[-[:alnum:]]+' | grep -vE 'latest-v[[:digit:]]+\.' | grep -oE 'latest[-[:alnum:]]+' | head -1)
downloadUri=https://nodejs.org/download/release/$releaseCodeName
fileName=$(curl -sL $downloadUri | grep -Eo 'node-v[[:digit:]]+\.[[:digit:]]+\.[[:digit:]]+\.tar\.gz' | head -1)
version=$(echo $fileName | grep -Eo '[[:digit:]]+\.[[:digit:]]+\.[[:digit:]]+')
installDirPath=/opt/node/$version
curl -LO $downloadUri/$fileName
tar -xzf $fileName
cd node-v$version
./configure --prefix=$installDirPath
make -j$(nproc)
make install

# Add the $installDirPath/bin directory to $PATH
echo 'PATH=$PATH:'$installDirPath/bin >> ~/.bashrc
# or
echo 'PATH=$PATH:'$installDirPath/bin >> ~/.zshrc
```


#### Windows

@TODO


### Step 2. Install packages

1) Install packages:
```
git clone https://github.com/morpho-os/framework.git
cd framework

# Install packages for the backend (server-side):
composer install
npm install -g gulp typescript stylus
npm install

# Install packages for the frontend (client-side):
cd public
npm install
```

2) Create virtual host for the 'public' directory, for example 'test'.

3) Open URI http://test in the browser, an installer should appear.


## Current build status

[![Build Status](https://travis-ci.org/morpho-os/framework.svg?branch=master)](https://travis-ci.org/morpho-os/framework)


## License

License is Apache-2.0.


## Who is behind Morpho and commercial support

Behind the Morpho framework is the [NeuroJazz Studio](http://neurojazz.com). We provide commercial support for the Morpho framework.
