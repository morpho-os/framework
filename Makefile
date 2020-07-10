serverModuleDirPath = $(CURDIR)/server
clientModuleDirPath = $(CURDIR)/client
errorOpts = --stop-on-error --stop-on-failure --stop-on-warning
defectOpts = --stop-on-error --stop-on-failure --stop-on-warning --stop-on-risky --stop-on-skipped --stop-on-incomplete

##############################################################################
# Assets

assets: js css

js:
	bin/build js

css:
	bin/build css

###############################################################################
# All tests

test:
	bin/test

test-stop:
	bin/test $(errorOpts)

###############################################################################
# Unit tests

utest:
	bin/test test/Unit/TestSuite.php

utest-stop:
	bin/test $(errorOpts) test/Unit/TestSuite.php

utest-stop-defect:
	bin/test $(defectOpts) test/Unit/TestSuite.php

###############################################################################
# Integration tests

itest:
	bin/test test/Integration/TestSuite.php

itest-stop:
	bin/test test/Integration/TestSuite.php $(errorOpts)

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
.PHONY: assets js css test test-stop utest utest-stop utest-stop-defect itest itest-stop mtest lint clear clean update setup
