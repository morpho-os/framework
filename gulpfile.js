'use strict';

const ts = require('gulp-typescript'),
    gulpUtil = require('gulp-util'),
    plumber = require('gulp-plumber'),
    rename = require('gulp-rename'),
    stylus = require('gulp-stylus'),
    gulp = require('gulp');

const tsFilePaths = 'web/resource/**/ts/*.ts';
const stylFilePaths = 'web/resource/**/styl/*.styl';

/*
 gulp-uglify Minify files with UglifyJS.
 gulp-umd
 gulp.spritesmith Convert a set of images into a spritesheet and CSS variables via gulp
 gulp-peg Gulp plugin for compiling PEG grammars
 gulp-ssh SSH and SFTP tasks for gulp
 gulp-uglifycss Gulp plugin to use uglifycss
*/

function transpile(path, transpiler, destDirName) {
    return gulp.src(path, {base: '.'})
        .pipe(plumber(function(error) {
            gulpUtil.log(gulpUtil.colors.red(error.toString()));
            this.emit('end');
        }))
        .pipe(transpiler())
        .pipe(plumber.stop())
        .pipe(rename(function (path) {
            var chunks = path.dirname.split('/');
            chunks.pop();
            path.dirname = chunks.join('/') + '/' + destDirName;
            //console.log(path.dirname);
        }))
        .pipe(gulp.dest('.'));
}

gulp.task('transpile-ts', function () {
    return transpile(tsFilePaths, function () {
        return ts({
            noImplicitAny: true,
            removeComments: true,
            target: 'ES5'
        })
    }, 'js');
});

gulp.task('transpile-styl', function () {
    transpile(stylFilePaths, stylus, 'css');
});

gulp.task('watch', function () {
    gulp.watch(tsFilePaths, ['transpile-ts']);
    gulp.watch(stylFilePaths, ['transpile-styl']);
});

gulp.task('default', ['watch']);
