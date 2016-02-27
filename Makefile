options := --noImplicitAny -t ES5 --moduleResolution classic --removeComments --suppressImplicitAnyIndexErrors --noEmitOnError --newLine LF
moduleDirPath := module
publicModuleDirPath := public/module

mainSrcFilePath = $(publicModuleDirPath)/$(1)/src/main.ts
destDirPath = $(publicModuleDirPath)/$(1)/dest
compileMainModuleFile = tsc $(options) --out $(call destDirPath,$(1))/main.js $(call mainSrcFilePath,$(1))
watchMainModuleFile = tsc $(options) -w --out $(call destDirPath,$(1))/main.js $(call mainSrcFilePath,$(1))

compile-ts: $(call mainSrcFilePath,system)
	@$(call compileMainModuleFile,system)
	@tsc $(options) --outDir $(call destDirPath,system) $(publicModuleDirPath)/system/src/test-case.ts

backend-test:
	@(cd test/server && phpunit)

compile-styl:
	@(cd $(publicModuleDirPath)/bootstrap/styl && stylus -c --disable-cache < main.styl > ../css/main.css)
	@(cd $(publicModuleDirPath)/bootstrap/styl && stylus -c --disable-cache < file-upload.styl > ../css/file-upload.css)
	@(cd $(publicModuleDirPath)/bootstrap/styl && stylus -c --disable-cache < file-upload-noscript.styl > ../css/file-upload-noscript.css)
	@(cd $(publicModuleDirPath)/system/styl && stylus -c --disable-cache < main.styl > ../css/main.css)

clean-tmp-js:
	@rm -f $(publicModuleDirPath)/**/dest/*
	@rm -f $(publicModuleDirPath)/**/src/*.d.ts
	@rm -f $(publicModuleDirPath)/**/src/*.js.map
	@rm -f $(publicModuleDirPath)/**/test/*.js
	@rm -f $(publicModuleDirPath)/**/src/**/*.js
	@rm -f $(publicModuleDirPath)/**/src/*.js

clean-cache:
	@rm -rf site/**/cache/*

update-npm-modules:
	@(cd public && npm update)

.PHONY: compile-ts backend-test clean-tmp-js update-npm-modules
