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

utest-stop-on-defect:
	bin/test --stop-on-error --stop-on-failure --stop-on-warning --stop-on-risky --stop-on-skipped --stop-on-incomplete test/unit/TestSuite.php

utest-stop-on-error:
	bin/test --stop-on-error --stop-on-failure --stop-on-warning test/unit/TestSuite.php

lint:
	php test/lint.php

clear:
clean:
	sudo rm -rf module/localhost/log/* module/localhost/cache/* test/functional/*.log

update:
	composer update
	# We use `install` instead of `update` to run the [scripts](https://docs.npmjs.com/misc/scripts#description) defined in the package.json file.
	cd public && npm install

.SILENT:
.PHONY: js css test ftest mtest utest utest-stop-on-defect utest-stop-on-error lint clear clean update
