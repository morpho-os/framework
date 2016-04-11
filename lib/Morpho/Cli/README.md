# morpho-os/cli

This package was automatically generated from the [morpho-os/framework](https://github.com/morpho-os/framework) package.


## Installation

```
cat > composer.json
{
    "require": [
        "morpho-os/cli": "dev-master"
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
            "url": "https://github.com/morpho-os/error"
        },
        {
            "type": "vcs",
            "url": "https://github.com/morpho-os/cli"
        }
    ]
}
# Press Ctrl + d to send EOF (End-Of-File) to the `cat` program.
^d
composer install
```