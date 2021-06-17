# Do not use make's built-in rules and variables (this increases performance and avoids hard-to-debug behaviour)
MAKEFLAGS += -rR

backendDirPath = $(CURDIR)/backend
frontendDirPath = $(CURDIR)/frontend

# Default target
all: test

################################################################################
# Tests

test:
	bin/test

# Unit tests
unit-test:
	bin/test test/Unit/TestSuite.php

integration-test:
	bin/test test/Integration/TestSuite.php

backend-test: module-test
module-test:
	bin/test $(backendDirPath)

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
	sass $(frontendDirPath)/localhost

watch-css:
	sass --watch $(frontendDirPath)/localhost

clean-css:
	find $(frontendDirPath)/localhost -mindepth 1 -name '*.css' -or -name '*.css.map' -not -path '*/node_modules/*' -print -delete

clean-js:
	find $(frontendDirPath)/localhost -mindepth 1 -not -path '*/node_modules/*' -and \( -name '*.js' -or -name '*.js.map' -or -name '*.tsbuildinfo' -or -name '*.d.ts' \) -not -path '*/lib/base/index.d.ts' -print -delete

clean-assets: clean-css clean-js

###############################################################################
# Docker

build:
	docker-compose build

################################################################################

clean: clean-assets
	sudo sh -c 'rm -rf test/Integration/*.log $(backendDirPath)/localhost/{log,cache}/*'

update:
	composer update
	# We use `install` instead of `update` to run the [scripts](https://docs.npmjs.com/misc/scripts#description) defined in the package.json file.
	cd public && npm install

init:
	composer require --dev psalm/plugin-phpunit && vendor/bin/psalm-plugin enable psalm/plugin-phpunit
	test -e package.json || echo '{}' > package.json
	npm install -g --save-dev @types/node
	npm install -g --save typescript@next concurrently

pull-peg:
	curl -sSfL https://raw.githubusercontent.com/python/cpython/main/Grammar/Tokens -o lib/Tech/Python/Tokens
	curl -sSfL https://raw.githubusercontent.com/python/cpython/main/Grammar/python.gram -o lib/Tech/Python/python.gram

# `help` taken from [containerd](https://github.com/containerd/containerd/blob/master/Makefile)
help: ## this help
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST) | sort

.SILENT:
.PHONY: all test unit-test integration-test backend-test module-test frontend-test lint assets js watch-js css watch-css clean-css clean-js clean-assets build clean update init pull-peg help