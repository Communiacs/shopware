module.exports = function (grunt) {
    var shopId = grunt.option('shopId') || 1,
        file = '../web/cache/config_' + shopId + '.json',
        config = grunt.file.readJSON(file),
        lessTargetFile = {},
        jsFiles = [],
        jsTargetFile = {},
        content = '',
        variables = {};

    lessTargetFile['../' + config.lessTarget] = '../web/cache/all.less';

    config['js'].forEach(function (item) {
        jsFiles.push('../' + item);
    });
    jsTargetFile['../' + config.jsTarget] = jsFiles;

    config['less'].forEach(function (item) {
        content += '@import "../' + item + '";';
        content += "\n";
    });
    grunt.file.write('../web/cache/all.less', content);

    for (var key in config.config) {
        variables[key] = config.config[key];
    }

    grunt.initConfig({
        uglify: {
            production: {
                options: {
                    compress: true,
                    preserveComments: false
                },
                files: jsTargetFile
            },
            development: {
                options: {
                    mangle: false,
                    compress: false,
                    beautify: true,
                    preserveComments: 'all'
                },
                files: jsTargetFile
            }
        },
        less: {
            production: {
                options: {
                    compress: true,
                    modifyVars: variables,
                    relativeUrls: true
                },
                files: lessTargetFile
            },
            development: {
                options: {
                    modifyVars: variables,
                    dumpLineNumbers: 'all',
                    relativeUrls: true,
                    sourceMap: true,
                    sourceMapFileInline: true,
                    sourceMapRootpath: '../'
                },
                files: lessTargetFile
            }
        },
        watch: {
            less: {
                files: [
                    '../engine/Shopware/Plugins/**/*.less',
                    '../themes/Frontend/**/*.less',
                    '../custom/plugins/**/*.less'
                ],
                tasks: ['less:development'],
                options: {
                    spawn: false
                }
            },
            js: {
                files: [
                    '../themes/Frontend/**/_public/src/js/*.js',
                    '../engine/Shopware/Plugins/**/frontend/**/src/js/**/*.js',
                    '../custom/plugins/**/frontend/**/src/js/**/*.js'
                ],
                tasks: ['uglify:development'],
                options: {
                    spawn: false
                }
            }
        },
        jshint: {
            options: {
                browser: true,
                force: true,
                globals: {
                    jQuery: true,
                    StateManager: true
                }
            },
            src: [
                'Gruntfile.js',
                '../themes/Frontend/**/_public/src/js/*.js',
                '../engine/Shopware/Plugins/**/frontend/**/src/js/**/*.js'
            ]
        }
    });

    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-chokidar');

    grunt.renameTask('chokidar', 'watch');
    grunt.registerTask('production', [ 'jshint', 'less:production', 'uglify:production' ]);
    grunt.registerTask('default', [ 'less:development', 'uglify:development', 'watch' ]);
};
