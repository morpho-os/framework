baseDirPath := $(realpath .)
moduleDirPath := $(baseDirPath)/module
publicDirPath = $(baseDirPath)/public
publicModuleDirPath := $(publicDirPath)/module

js:
	bin/tsc $(publicModuleDirPath)

css:
	# To compress add the `-c` option
	cd $(publicModuleDirPath)/system/rc/css && stylus -I $(publicDirPath)/node_modules/bootstrap-styl --disable-cache < index.styl > index.css

test:
	bin/test

ftest:
	bin/test test/functional/TestSuite.php

mtest:
	bin/test module

utest:
	bin/test test/unit/TestSuite.php

clean:
	sudo rm -rf module/localhost/log/* module/localhost/cache/* test/functional/*.log

update:
	composer update
	# We use `install` instead of `update` to run the [scripts](https://docs.npmjs.com/misc/scripts#description) defined in the package.json file.
	cd public && npm install

reset: clean
	sudo rm -f module/localhost/config/config.php

.SILENT:
.PHONY: js css test ftest mtest utest clean update reset
