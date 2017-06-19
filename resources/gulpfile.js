var gulp = require('gulp'),
	sourcemaps = require('gulp-sourcemaps'),
	
	// Less
	less = require("gulp-less"),
	LessPluginAutoPrefix = require('less-plugin-autoprefix'),
	cleanCss = require('gulp-clean-css'),
	autoprefixer = new LessPluginAutoPrefix({ browsers: ["last 3 versions"] }),
	
	// JS
	rollup = require('rollup').rollup,
	eslint = require('rollup-plugin-eslint'),
	babel  = require('rollup-plugin-babel'),
	uglify = require('rollup-plugin-uglify'),
	nodeResolve = require('rollup-plugin-node-resolve'),
	commonjs = require('rollup-plugin-commonjs'),
	minify = require('uglify-js').minify;

// JS
function rl (i, o) {
	rollup({
		entry: i,
		plugins: [
			eslint({
				useEslintrc: false,
				baseConfig: {
					parserOptions: {
						ecmaVersion: 7,
						sourceType: "module"
					},
					extends: "eslint:recommended",
				},
				parser: "babel-eslint",
				rules: {
					eqeqeq: [1, "smart"],
					semi: [1, "always"],
					"no-loop-func": [2],
					"no-console": [1],
					"no-mixed-spaces-and-tabs": [0],
				},
				envs: ["browser", "es6"]
			}),
			nodeResolve({
				module: true,
				jsnext: true,
				main: true,
				browser: true
			}),
			babel(),
			commonjs(),
			uglify({}, minify)
		],
		sourceMap: true
	}).then(function (bundle) {
		bundle.write({
			format: 'es',
			sourceMap: true,
			dest: o
		});
	}).catch(function(err) { console.error(err); });
}

gulp.task('js', function () {
	rl("js/seo-field.js", "../seo/resources/js/seo-field.min.js");
	rl("js/seo-settings.js", "../seo/resources/js/seo-settings.min.js");
});

// Less
gulp.task('less', function () {
	gulp.src('less/**/*.less')
	    .pipe(sourcemaps.init())
	    .pipe(less({
		    plugins: [autoprefixer]
	    }).on('error', function(err){ console.log(err.message); }))
	    .pipe(cleanCss())
	    .pipe(sourcemaps.write('.'))
	    .pipe(gulp.dest('../seo/resources/css'));
});

// Watchers
gulp.task('watch', function () {
	gulp.watch(['js/**/*.js', '!js/**/*.min.js'], ['js']);
	gulp.watch(['less/**/*'], ['less']);
});

gulp.task('default', ['watch']);