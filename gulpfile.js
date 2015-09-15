'use strict';

const ts = require('gulp-typescript'),
    gulpUtil = require('gulp-util'),
    plumber = require('gulp-plumber'),
    rename = require('gulp-rename'),
    stylus = require('gulp-stylus'),
    gulp = require('gulp'),
    fs = require('fs');

/*
 gulp-uglify Minify files with UglifyJS.
 gulp-umd
 gulp.spritesmith Convert a set of images into a spritesheet and CSS variables via gulp
 gulp-peg Gulp plugin for compiling PEG grammars
 gulp-ssh SSH and SFTP tasks for gulp
 gulp-uglifycss Gulp plugin to use uglifycss
*/

function transpile(sourcePath, transpiler, newDirName) {
    function replaceLastDirName(dirPath, newDirName) {
        var chunks = dirPath.split('/');
        chunks.pop();
        return chunks.join('/') + '/' + newDirName;
    }

    return gulp.src(sourcePath, {base: '.'})
        .pipe(plumber(function(error) {
            gulpUtil.log(gulpUtil.colors.red(error.toString()));
            this.emit('end');
        }))
        .pipe(transpiler())
        .pipe(rename(function (entryPath) {
            entryPath.dirname = replaceLastDirName(entryPath.dirname, newDirName);
        }))
        .pipe(plumber.stop())
        .pipe(gulp.dest('.'));
}

function watchWith(transpiler, settings) {
    function onChange(changeMeta) {
        if (!changeMeta.path) {
            throw new Error("Unable to get the 'path' component");
        }
        let filePath = changeMeta.path;
        if (!fs.lstatSync(filePath).isFile()) {
            throw new Error("The changed entry is not a file");
        }
        transpile(filePath, transpiler, settings.destDirName);
    }

    return function () {
        gulp.watch(settings.sourcePath, onChange);
    }
}

function transpileWith(transpiler, settings) {
    return function () {
        transpile(settings.sourcePath, transpiler, settings.destDirName);
    }
}

const stylSettings = {
    sourcePath: moduleDirPath + '/**/styl/*.styl',
    destDirName: 'css'
};

gulp.task('transpile-styl', transpileWith(stylus, stylSettings));
gulp.task('watch-styl', watchWith(stylus, stylSettings));
/*
gulp.task('transpile-ts', transpileWith(transpileTs, tsSettings));
gulp.task('watch-ts', watchWith(transpileTs, tsSettings));
*/

gulp.task('transpile', [/*'transpile-ts', */'transpile-styl']);
gulp.task('watch', [/*'watch-ts', */'watch-styl']);

gulp.task('default', ['transpile', 'watch']);
