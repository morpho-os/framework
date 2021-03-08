backendDirPath = $(CURDIR)/backend
frontendDirPath = $(CURDIR)/frontend

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
	bin/test $(testOpts) $(backendDirPath)

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
	bin/ts build

watch-js:
	bin/ts watch

css:
	sass $(frontendDirPath)/localhost/rc/css:$(frontendDirPath)/localhost/rc/css

################################################################################

clear: clean
clean:
	sudo sh -c 'rm -rf test/Integration/*.log $(backendDirPath)/localhost/{log,cache}/*'
	find $(frontendDirPath)/localhost -mindepth 1 -not -path '*/node_modules/*' -and \( -name '*.js' -or -name '*.js.map' -or -name '*.tsbuildinfo' -or -name '*.css' -or -name '*.d.ts' \) -and ! -name 'index.d.ts' -delete

update:
	composer update
	# We use `install` instead of `update` to run the [scripts](https://docs.npmjs.com/misc/scripts#description) defined in the package.json file.
	cd public && npm install

setup:
	composer require --dev psalm/plugin-phpunit && vendor/bin/psalm-plugin enable psalm/plugin-phpunit
	test -e package.json || echo '{}' > package.json
	npm install -g --save-dev @types/node
	npm install -g --save typescript@next concurrently

.SILENT:
.PHONY: all test unit-test integration-test backend-test module-test frontend-test lint assets js watch-js css clear clean update setup
