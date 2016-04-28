var gulp = require('gulp'),
	uglify = require("gulp-uglify"),
	jshint = require("gulp-jshint"),
	rename = require("gulp-rename"),
	less = require("gulp-less"),
	LessPluginCleanCSS = require('less-plugin-clean-css'),
	cleancss = new LessPluginCleanCSS({ advanced: true }),
	LessPluginAutoPrefix = require('less-plugin-autoprefix'),
	autoprefixer = new LessPluginAutoPrefix({ browsers: ["last 2 versions"] });

// JS
gulp.task('js', function () {
	gulp.src(['js/**/*.js', '!js/**/*.min.js'])
		.pipe(jshint())
		.pipe(jshint.reporter())
		.pipe(uglify().on('error', function(err){ console.log(err.message); }))
		.pipe(rename({ suffix: ".min" }))
		.pipe(gulp.dest('js'));
});

// Less
gulp.task('less', function () {
	gulp.src('less/**/*.less')
		.pipe(less({
			plugins: [autoprefixer, cleancss]
		}).on('error', function(err){ console.log(err.message); }))
		.pipe(gulp.dest('./css'));
});

// Watchers
gulp.task('watch', function () {
	gulp.watch(['js/**/*.js', '!js/**/*.min.js'], ['js']);
	gulp.watch(['less/**/*'], ['less']);
});

gulp.task('default', ['watch']);