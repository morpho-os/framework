# morpho-os/test

This package was automatically generated from the [morpho-os/framework](https://github.com/morpho-os/framework) package.


## Installation

```
cat > composer.json
{
    "require": [
        "morpho-os/test": "dev-master"
    ],
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/morpho-os/base"
        },
        {
            "type": "vcs",
            "url": "https://github.com/morpho-os/db"
        },
        {
            "type": "vcs",
            "url": "https://github.com/morpho-os/fs"
        },
        {
            "type": "vcs",
            "url": "https://github.com/morpho-os/test"
        }
    ]
}
# Press Ctrl + d to send EOF (End-Of-File) to the `cat` program.
^d
composer install
```