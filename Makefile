baseDirPath := $(realpath .)
moduleDirPath := $(baseDirPath)/module
publicDirPath = $(baseDirPath)/public
publicModuleDirPath := $(publicDirPath)/module

js:
	bin/tsc $(publicModuleDirPath)

css:
	# To compress add the `-c` option
	cd $(publicModuleDirPath)/system/rc/css && stylus -I $(publicDirPath)/node_modules/bootstrap-styl --disable-cache < main.styl > main.css

test:
	bin/test

func-test:
	bin/test --bootstrap $(baseDirPath)/test/bootstrap.php test/functional/TestSuite.php

unit-test:
	bin/test --bootstrap $(baseDirPath)/test/bootstrap.php test/unit/TestSuite.php

clean:
	sudo rm -rf module/localhost/log/* module/localhost/cache/*

update:
	composer update
	# We use `install` instead of `update` to run the [scripts](https://docs.npmjs.com/misc/scripts#description) defined in the package.json file.
	cd public && npm install

reset: clean
	sudo rm -f module/localhost/config/config.php

.SILENT:
.PHONY: js css test func-test clean update reset
