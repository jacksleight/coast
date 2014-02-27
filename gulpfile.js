var gulp = require('gulp')
	phpunit = require('gulp-phpunit');

gulp.task('phpunit', function() {
    gulp.src('tests/**/*.php')
    	.pipe(phpunit('', {clear: true}));
});

gulp.task('watch', function() {
	gulp.watch([
		'lib/**/*.php',
		'tests/**/*.php'
	], ['phpunit']);
});