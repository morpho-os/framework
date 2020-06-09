serverModuleDirPath = $(CURDIR)/server
clientModuleDirPath = $(CURDIR)/client

assets: js css

js:
	bin/build ts

css:
	bin/build css

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
# Integration tests

itest:
	bin/test test/Integration/TestSuite.php

itest-stop-on-error:
	bin/test test/Integration/TestSuite.php --stop-on-error --stop-on-failure --stop-on-warning

###############################################################################
# Module tests

mtest:
	bin/test server

lint:
	php test/lint.php

clear: clean
clean:
	sudo sh -c 'rm -rf test/Integration/*.log $(serverModuleDirPath)/localhost/{log,cache}/*'
	find $(clientModuleDirPath)/localhost -mindepth 1 -not -path '*/node_modules/*' -and \( -name '*.js' -or -name '*.js.map' -or -name '*.tsbuildinfo' -or -name '*.css' -or -name '*.d.ts' \) -and ! -name 'index.d.ts' -delete

update:
	composer update
	# We use `install` instead of `update` to run the [scripts](https://docs.npmjs.com/misc/scripts#description) defined in the package.json file.
	cd public && npm install

setup:
	npm install -g stylus typescript@next

.SILENT:
.PHONY: assets js css test test-stop-on-error utest utest-stop-on-defect utest-stop-on-error itest itest-stop-on-error mtest lint clear clean update setup
