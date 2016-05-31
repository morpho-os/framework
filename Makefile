options := --noImplicitAny -t ES5 --moduleResolution classic --removeComments --suppressImplicitAnyIndexErrors --noEmitOnError --newLine LF
moduleDirPath := module
publicModuleDirPath := public/module

mainSrcFilePath = $(publicModuleDirPath)/$(1)/src/main.ts
destDirPath = $(publicModuleDirPath)/$(1)/dest
compileMainModuleFile = tsc $(options) --out $(call destDirPath,$(1))/main.js $(call mainSrcFilePath,$(1))
watchMainModuleFile = tsc $(options) -w --out $(call destDirPath,$(1))/main.js $(call mainSrcFilePath,$(1))

ts: $(call mainSrcFilePath,system)
	@$(call compileMainModuleFile,system)
	@tsc $(options) --outDir $(call destDirPath,system) $(publicModuleDirPath)/system/src/test-case.ts

styl:
	@(cd $(publicModuleDirPath)/bootstrap/styl && stylus -c --disable-cache < main.styl > ../css/main.css)
	@(cd $(publicModuleDirPath)/bootstrap/styl && stylus -c --disable-cache < file-upload.styl > ../css/file-upload.css)
	@(cd $(publicModuleDirPath)/bootstrap/styl && stylus -c --disable-cache < file-upload-noscript.styl > ../css/file-upload-noscript.css)
	@(cd $(publicModuleDirPath)/system/styl && stylus -c --disable-cache < main.styl > ../css/main.css)

backend-test:
	@(cd test/server && phpunit)

clean-js:
	@rm -f $(publicModuleDirPath)/**/dest/*
	@rm -f $(publicModuleDirPath)/**/src/*.d.ts
	@rm -f $(publicModuleDirPath)/**/src/*.js.map
	@rm -f $(publicModuleDirPath)/**/test/*.js
	@rm -f $(publicModuleDirPath)/**/src/**/*.js
	@rm -f $(publicModuleDirPath)/**/src/*.js

clean-site-cache:
	@rm -rf site/**/cache/*

update-npm-modules:
	@(cd public && npm update)

.PHONY: ts styl backend-test clean-js clean-site-cache update-npm-modules