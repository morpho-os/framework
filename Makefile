baseDirPath := $(realpath .)
moduleDirPath := $(baseDirPath)/module
publicDirPath = $(baseDirPath)/public
publicModuleDirPath := $(publicDirPath)/module

js:
	bin/compile-ts $(publicModuleDirPath)

css:
	# To compress add the `-c` option
	cd $(publicModuleDirPath)/system/rc/css && stylus -I $(publicDirPath)/node_modules/bootstrap-styl --disable-cache < main.styl > main.css

test:
	bin/run-tests

#clean: clean-site-cache
#	rm -f $(publicModuleDirPath)/**/dest/*
#	rm -f $(publicModuleDirPath)/**/src/*.d.ts
#	rm -f $(publicModuleDirPath)/**/src/*.js.map
#	rm -f $(publicModuleDirPath)/**/test/*.js
#	rm -f $(publicModuleDirPath)/**/src/**/*.js
#	rm -f $(publicModuleDirPath)/**/src/*.js

clean-site-cache:
	rm -rf site/**/cache/*

update:
	composer update
	# We use `install` instead of `update` to run the [scripts](https://docs.npmjs.com/misc/scripts#description) defined in the package.json file.
	cd public && npm install

.SILENT:
.PHONY: js css test clean-site-cache update
