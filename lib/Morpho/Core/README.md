# morpho-os/core

This package was automatically generated from the [morpho-os/framework](https://github.com/morpho-os/framework) package.


## Installation

```
cat > composer.json
{
    "require": [
        "morpho-os/core": "dev-master"
    ],
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/morpho-os/base"
        },
        {
            "type": "vcs",
            "url": "https://github.com/morpho-os/code"
        },
        {
            "type": "vcs",
            "url": "https://github.com/morpho-os/db"
        },
        {
            "type": "vcs",
            "url": "https://github.com/morpho-os/di"
        },
        {
            "type": "vcs",
            "url": "https://github.com/morpho-os/fs"
        },
        {
            "type": "vcs",
            "url": "https://github.com/morpho-os/core"
        }
    ]
}
# Press Ctrl + d to send EOF (End-Of-File) to the `cat` program.
^d
composer install
```