baseDirPath := $(realpath .)
moduleDirPath := $(baseDirPath)/module
publicDirPath = $(baseDirPath)/public
publicModuleDirPath := $(publicDirPath)/module

makeTargets = $(shell grep -oP '^[A-Za-z0-9_-]+:' Makefile | tr -d :)

targets:
	echo Targets in the alphabetical order:
	echo $(makeTargets) | tr ' ' '\n' | sed 's/^/  ・ /' | sort

phony:
	sed -i  's/^\.PHONY:.*$$/.PHONY: $(makeTargets)/' Makefile

js:
	bin/compile-ts $(publicModuleDirPath)

css:
    # To compress add the `-c` option
	cd $(publicModuleDirPath)/system/rc/css && stylus -I $(publicDirPath)/node_modules/bootstrap-styl --disable-cache < main.styl > main.css

test:
	cd test/server && phpunit

#clean: clean-site-cache
#	rm -f $(publicModuleDirPath)/**/dest/*
#	rm -f $(publicModuleDirPath)/**/src/*.d.ts
#	rm -f $(publicModuleDirPath)/**/src/*.js.map
#	rm -f $(publicModuleDirPath)/**/test/*.js
#	rm -f $(publicModuleDirPath)/**/src/**/*.js
#	rm -f $(publicModuleDirPath)/**/src/*.js

clean-site-cache:
	rm -rf site/**/cache/*

npm-update:
	# We use `install` instead of `update` to run the [scripts](https://docs.npmjs.com/misc/scripts#description) defined in the package.json file.
	cd public && npm install

.SILENT:
.PHONY: targets phony js css test clean clean-site-cache npm-update
