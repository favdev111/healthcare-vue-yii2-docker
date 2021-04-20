var gulp = require('gulp'),
    plugins = require('gulp-load-plugins')(),
    postcss = require('gulp-postcss'),
    assets  = require('postcss-assets'),
    plumber = require('gulp-plumber'),
    babel = require('gulp-babel'),
    rename = require('gulp-rename'),
    merge  = require('merge2'),
    fs = require('fs'),
    environments = require('gulp-environments'),
    production = environments.production,
    development = environments.development;

// server connect
gulp.task('connect', function () {
    plugins.connect.server({
        root: './',
        livereload: true,
        host: '127.0.0.1',
        port: 8000
    });
});

// styles
gulp.task('styles', function () {
    var processors = [
        require('postcss-import')(),
        require('postcss-nested')(),
        require('postcss-mixins')(),
        require('postcss-preset-env')(),
        require('postcss-short')(),
        require('postcss-css-variables')(),
        require('postcss-custom-properties')(),
        require('postcss-color-function')(),
        require('cssnano')(),
        require('precss')(),
        require('autoprefixer')({browsers: ['last 2 versions']}),
    ];

    gulp.src('src/css/**/*')
        .pipe(plugins.changed('dist/css'))
        .pipe(gulp.dest('dist/css'));

    gulp.src('src/fonts/**/*')
        .pipe(plugins.changed('dist/fonts'))
        .pipe(gulp.dest('dist/fonts'));

    return gulp.src('src/styles/main.pcss')
        .pipe(plumber({
          errorHandler: function (error) {
              console.log(error);
              this.emit('end');
          }
        }))
        .pipe(postcss(processors))
        .pipe(postcss([assets({
            loadPaths: ['**']
        })]))
        .pipe(development(plugins.sourcemaps.write('.')))
        .pipe(plugins.rename('style.min.css'))
        .pipe(gulp.dest('dist/css'))
        .pipe(development(plugins.connect.reload()));
});

gulp.task('video', function () {
    return gulp.src('src/video/**/*')
        .pipe(plugins.changed('dist/video'))
        .pipe(gulp.dest('dist/video'));
});

var retinizeOpts = {
    /// Your options here.
};

// html
gulp.task('html', function() {
    gulp.src('*.html')
        .pipe(plugins.connect.reload());
});

// gulp-image
gulp.task('images', function () {
    gulp.src('src/favicon/**/*').pipe(gulp.dest('dist/favicon'));

    return gulp.src('src/img/**/*')
        .pipe(plugins.changed('dist/img'))
        .pipe(plugins.image())
        .pipe(plugins.retinize(retinizeOpts))
        .pipe(gulp.dest('dist/img'))
        .pipe(development(plugins.connect.reload()));
});

// minfy js
gulp.task('js', function () {
    function createErrorHandler(name) {
        return function (err) {
            console.error('Error from ' + name + ' in compress task', err.toString());
        };
    }

    return gulp.src(['src/js/**/*'])
        .pipe(babel({
            presets: ['@babel/env']
        }))
        .pipe(production(plugins.uglify()))
        .on('error', createErrorHandler('uglify'))
        .pipe(gulp.dest('dist/js'));
});

let libs = [];
if (fs.existsSync('libs.json')) {
    libs = JSON.parse(fs.readFileSync('libs.json'));
}

gulp.task('libs', function () {
    const tasks = [
        gulp.src(['src/libs/**/*']).pipe(gulp.dest('dist/libs'))
    ];
    for (const property in libs) {
        if (libs.hasOwnProperty(property)) {
            let entry = libs[property];
            tasks.push(
              gulp.src('../../node_modules/' + entry.src)
                  .pipe(rename(entry.destName))
                  .pipe(gulp.dest('dist/libs'))
            );
        }
    }

    return merge(tasks);
});

// watch
gulp.task('watch', function () {
    gulp.watch('src/styles/**/*.pcss', gulp.parallel('styles'));
    gulp.watch('src/img/**/*', gulp.parallel('images'));
    gulp.watch('src/**/*.js', gulp.parallel('js'));
    gulp.watch('*.html', gulp.parallel('html'));
    gulp.watch('libs.json', gulp.parallel('libs'));
});

// gulp.task('default', ['html', 'styles', 'images', 'js', 'watch', 'video']);
// gulp.task('design', ['html', 'styles', 'connect','images', 'js', 'watch', 'video']);
// gulp.task('default', gulp.parallel('html', 'styles', 'images', 'js', 'watch', 'video'));

gulp.task('default',
  gulp.series('styles', 'images', 'js', 'video', 'watch', gulp.parallel('html', 'styles', 'images', 'js', 'video'))
);
gulp.task('js-css',
  gulp.series('styles', 'js', 'watch', gulp.parallel('html', 'styles', 'js'))
);
gulp.task('build', gulp.parallel('styles', 'images', 'js', 'video'));
