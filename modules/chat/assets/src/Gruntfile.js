'use strict';
/***************************************************************************
 run task - "grunt build --env=prod --target=mobile" for mobile
 1) task - "grunt build"
 > use configs from ../q-municate-web./app/config.js

 2) task - "grunt build --env=dev"
 > use configs from ../q-municate-web./app/configs/environments.js and set DEV environment

 3) task - "grunt build --env=prod"
 > use configs from ../q-municate-web./app/configs/environments.js and set PROD environment
 ***************************************************************************/
var SERVER_PORT = 9000;

// # Globbing

module.exports = function(grunt) {
    var appTarget = grunt.option('target') || 'front';
    var yeomanConfig, envTarget;

    // show elapsed time at the end
    require('time-grunt')(grunt);
    // load all grunt tasks
    require('load-grunt-tasks')(grunt);

    // configurable paths
    yeomanConfig = {
        app: 'app',
        dist: 'dist',
        appSuffix: (appTarget === 'mobile' ? 'mobile' : ''),
    };

    grunt.initConfig({
        yeoman: yeomanConfig,
        pkg: grunt.file.readJSON('bower.json'),

    clean: {
        dev: ['.sass-cache', '.tmp', '<%= yeoman.app %>/.css<%= yeoman.appSuffix %>'],
        dist: ['.sass-cache', '.tmp', '<%= yeoman.app %>/.css<%= yeoman.appSuffix %>',
            '<%= yeoman.dist %>/scripts<%= yeoman.appSuffix %>', '<%= yeoman.dist %>/styles<%= yeoman.appSuffix %>', '<%= yeoman.dist %>/vendor'
        ],
        tmpBuild: ['<%= yeoman.app %>/scripts<%= yeoman.appSuffix %>/build.js']
    },

        compass: {
            compile: {
                options: {
                    cssDir: '<%= yeoman.app %>/.css<%= yeoman.appSuffix %>',
                    sassDir: '<%= yeoman.app %>/styles<%= yeoman.appSuffix %>',
                    javascriptsDir: '<%= yeoman.app %>/scripts<%= yeoman.appSuffix %>',
                    imagesDir: '<%= yeoman.app %>/images',
                    noLineComments: true,
                    relativeAssets: true,
                    raw: 'preferred_syntax = :scss\n'
                }
            }
        },

        bower: {
            all: {
                rjsConfig: '<%= yeoman.app %>/scripts<%= yeoman.appSuffix %>/main.js',
                options: {
                    exclude: ['jquery', 'modernizr', 'requirejs']
                }
            }
        },

        requirejs: {
            dist: {
                options: {
                    baseUrl: '<%= yeoman.app %>/scripts<%= yeoman.appSuffix %>',
                    mainConfigFile: "<%= yeoman.app %>/scripts<%= yeoman.appSuffix %>/main.js",
                    name: 'main',
                    optimize: 'none',
                    out: "<%= yeoman.app %>/scripts<%= yeoman.appSuffix %>/build.js",
                    include: ['../bower_components/requirejs/require.js'],
                    almond: false,
                    preserveLicenseComments: false
                }
            }
        },

        watch: {
            options: {
                spawn: false
            },
            css: {
                files: ['<%= yeoman.app %>/styles/{,*/}*.scss'],
                tasks: ['compass']
            },
        },

        useminPrepare: {
            html: '<%= yeoman.app %>/index.html',
            options: {
                dest: '<%= yeoman.dist %>'
            }
        },

        cssmin: {
            target: {
                files: {
                    '<%= yeoman.dist %>/styles<%= yeoman.appSuffix %>/build.css': ['<%= yeoman.app %>/.css<%= yeoman.appSuffix %>/main.css']
                }
            }
        },

        uglify: {
            options: {
                // beautify: true,
                // sourceMapIncludeSources: true,
                // sourceMap: true
            },
            target : {
                files: {
                    '<%= yeoman.dist %>/scripts<%= yeoman.appSuffix %>/build.js': ['<%= yeoman.app %>/scripts<%= yeoman.appSuffix %>/build.js']
                }
            }
        },

        imagemin: {
            dist: {
                files: [{
                    expand: true,
                    cwd: '<%= yeoman.app %>/images',
                    src: '{,*/}*.{png,jpg,jpeg,svg,gif}',
                    dest: '<%= yeoman.dist %>/images'
                }]
            }
        },

        htmlmin: {
            dist: {
                files: [{
                    expand: true,
                    cwd: '<%= yeoman.app %>',
                    src: '*.html',
                    dest: '<%= yeoman.dist %>'
                }]
            }
        },

        usemin: {
            html: ['<%= yeoman.dist %>/{,*/}*.html'],
            options: {
                dirs: ['<%= yeoman.dist %>']
            }
        },

        copy: {
            dist: {
                files: [{
                    expand: true,
                    cwd: '<%= yeoman.app %>',
                    src: [
                        '*.{ico,png}',
                        'audio/{,*/}*.*',
                        'fonts/{,*/}*.*'
                    ],
                    dest: '<%= yeoman.dist %>'
                }]
            }
        },

        connect: {
            options: {
                protocol: 'https',
                port: grunt.option('port') || SERVER_PORT,
                open: true,
                // change this to '0.0.0.0' to access the server from outside
                hostname: '0.0.0.0'
            },
            dev: {
                options: {
                    base: [
                        '.tmp',
                        '<%= yeoman.app %>'
                    ]
                }
            },
            dist: {
                options: {
                    protocol: 'https',
                    base: '<%= yeoman.dist %>'
                }
            }
        },

        jshint: {
            options: {
                jshintrc: '.jshintrc',
                reporter: require('jshint-stylish')
            },
            all: [
                'Gruntfile.js',
                '<%= yeoman.app %>/scripts<%= yeoman.appSuffix %>/{,*/}*.js',
                '!<%= yeoman.app %>/vendor/*'
            ]
        },

        includereplace: {
            prod: {
                src: '<%= yeoman.app %>/configs/environment.js',
                dest: '<%= yeoman.app %>/configs/main_config.js'
            },
            dev: {
                src: '<%= yeoman.app %>/configs/environment.js',
                dest: '<%= yeoman.app %>/configs/main_config.js'
            },
            local: {
                src: '<%= yeoman.app %>/config.js',
                dest: '<%= yeoman.app %>/configs/main_config.js'
            }
        },
    });

    envTarget = grunt.option('env') || 'local';

    grunt.registerTask('server', function(target) {
        grunt.log.warn('The `server` task has been deprecated. Use `grunt serve` to start a server.');
        grunt.task.run(['serve' + (target ? ':' + target : '')]);
    });

    grunt.registerTask('serve', function(target) {
        if (target === 'dist') {
            return grunt.task.run(['build', 'connect:dist:keepalive']);
        }

        /***********************************************************************
         1) task - "grunt serve"
         > use configs from ../q-municate-web./app/config.js

         2) task - "grunt serve --env=dev"
         > use configs from ../q-municate-web./app/configs/environments.js and set DEV environment

         3) task - "grunt serve --env=prod"
         > use configs from ../q-municate-web./app/configs/environments.js and set PROD environment
         ***********************************************************************/
        grunt.task.run([
            'includereplace:' + envTarget,
            'clean:dev',
            'compass',
            'handlebars',
            'connect:dev',
            'watch'
        ]);
    });

    /***************************************************************************
     1) task - "grunt build"
     > use configs from ../q-municate-web./app/config.js

     2) task - "grunt build --env=dev"
     > use configs from ../q-municate-web./app/configs/environments.js and set DEV environment

     3) task - "grunt build --env=prod"
     > use configs from ../q-municate-web./app/configs/environments.js and set PROD environment
     ***************************************************************************/
    grunt.registerTask('build', [
        // 'jshint',
        'includereplace:' + envTarget,
        'clean:dist',
        'compass',
        'requirejs',
        'useminPrepare',
        'concat',
        'cssmin',
        'uglify',
        'newer:imagemin',
        'htmlmin',
        // 'rev',
        'usemin',
        'newer:copy',
    ]);

    grunt.registerTask('default', ['build']);
    grunt.registerTask('test', ['jshint']);
};


