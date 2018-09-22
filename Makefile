baseDirPath := $(realpath .)
moduleDirPath := $(baseDirPath)/module
publicDirPath = $(baseDirPath)/public
publicModuleDirPath := $(publicDirPath)/module

js:
	bin/tsc $(publicModuleDirPath)

css:
	# To compress add the `-c` option
	cd $(publicModuleDirPath)/system/rc/css && stylus -I $(publicDirPath)/node_modules/bootstrap-styl --disable-cache < index.styl > index.css

###############################################################################
# All tests

test:
	bin/test

test-stop-on-error:
	bin/test --stop-on-error --stop-on-failure --stop-on-warning

###############################################################################
# Unit tests

utest:
	bin/test test/Unit/TestSuite.php

utest-stop-on-defect:
	bin/test --stop-on-error --stop-on-failure --stop-on-warning --stop-on-risky --stop-on-skipped --stop-on-incomplete test/Unit/TestSuite.php

utest-stop-on-error:
	bin/test --stop-on-error --stop-on-failure --stop-on-warning test/Unit/TestSuite.php

###############################################################################
# Acceptance tests

itest:
	bin/test test/Integration/TestSuite.php

itest-stop-on-error:
	bin/test test/Integration/TestSuite.php --stop-on-error --stop-on-failure --stop-on-warning

###############################################################################
# Module tests

mtest:
	bin/test module


lint:
	php test/lint.php

clear: clean
clean:
	sudo rm -rf module/localhost/log/* module/localhost/cache/* test/Integration/*.log

update:
	composer update
	# We use `install` instead of `update` to run the [scripts](https://docs.npmjs.com/misc/scripts#description) defined in the package.json file.
	cd public && npm install

.SILENT:
.PHONY: js css test test-stop-on-error utest utest-stop-on-defect utest-stop-on-error itest itest-stop-on-error mtest lint clear clean update
