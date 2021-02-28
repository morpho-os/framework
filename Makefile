backendModuleDirPath = $(CURDIR)/server
frontendModuleDirPath = $(CURDIR)/client

# Default target
all: test

################################################################################
# Tests

strictOpts := --stop-on-warning --stop-on-failure --stop-on-error --stop-on-skipped --stop-on-incomplete --stop-on-risky --fail-on-incomplete --fail-on-risky --fail-on-skipped --fail-on-warning
looseOpts := --stop-on-warning --stop-on-failure --stop-on-error --stop-on-risky --fail-on-risky --fail-on-skipped --fail-on-warning
# todo: switch to strictOpts after solving the #8
testOpts := $(looseOpts)

test:
	bin/test $(testOpts)

# Unit tests
unit-test:
	bin/test $(testOpts) test/Unit/TestSuite.php

integration-test:
	bin/test $(testOpts) test/Integration/TestSuite.php

backend-test: module-test
module-test:
	bin/test $(testOpts) $(backendModuleDirPath)

# todo: frontend tests
frontend-test:
	echo todo
	exit 1

lint:
	php test/lint.php

###############################################################################
# Assets

assets: js css

js:
	bin/build js

css:
	bin/build css

################################################################################

clear: clean
clean:
	sudo sh -c 'rm -rf test/Integration/*.log $(backendModuleDirPath)/localhost/{log,cache}/*'
	find $(frontendModuleDirPath)/localhost -mindepth 1 -not -path '*/node_modules/*' -and \( -name '*.js' -or -name '*.js.map' -or -name '*.tsbuildinfo' -or -name '*.css' -or -name '*.d.ts' \) -and ! -name 'index.d.ts' -delete

update:
	composer update
	# We use `install` instead of `update` to run the [scripts](https://docs.npmjs.com/misc/scripts#description) defined in the package.json file.
	cd public && npm install

setup:
	composer require --dev psalm/plugin-phpunit && vendor/bin/psalm-plugin enable psalm/plugin-phpunit
	npm install -g typescript@next

.SILENT:
.PHONY: all assets js css test test-stop utest utest-stop utest-stop-defect itest itest-stop mtest lint clear clean update setup
