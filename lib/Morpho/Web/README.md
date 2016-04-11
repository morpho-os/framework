# morpho-os/web

This package was automatically generated from the [morpho-os/framework](https://github.com/morpho-os/framework) package.


## Installation

```
cat > composer.json
{
    "require": [
        "morpho-os/web": "dev-master"
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
            "url": "https://github.com/morpho-os/core"
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
            "url": "https://github.com/morpho-os/error"
        },
        {
            "type": "vcs",
            "url": "https://github.com/morpho-os/fs"
        },
        {
            "type": "vcs",
            "url": "https://github.com/morpho-os/web"
        }
    ]
}
# Press Ctrl + d to send EOF (End-Of-File) to the `cat` program.
^d
composer install
```