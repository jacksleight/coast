var gulp    = require('gulp');
var gutil   = require('gulp-util');
var phpunit = require('gulp-phpunit');
var plumber = require('gulp-plumber');

var errorHandler = function(error) {
    gutil.log(error.toString());
    this.emit('end');
};

gulp.task('phpunit', function() {
    gulp.src('tests/**/*.php')
        .pipe(plumber({errorHandler: errorHandler}))
    	.pipe(phpunit('', {clear: true, noCoverage: true}));
});

gulp.task('watch', function() {
	gulp.watch([
		'library/**/*.php',
		'tests/**/*.php'
	], ['phpunit']);
});