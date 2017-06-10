module.exports = function (grunt) {
    var shopId = grunt.option('shopId') || 1,
        file = '../web/cache/config_' + shopId + '.json',
        config = grunt.file.readJSON(file),
        lessTargetFile = {},
        jsFiles = [],
        jsTargetFile = {},
        content = '';

    lessTargetFile['../' + config.lessTarget] = '../web/cache/all.less';

    config.js.forEach(function (item) {
        jsFiles.push('../' + item);
    });
    jsTargetFile['../' + config.jsTarget] = jsFiles;

    for (var key in config.config) {
        content += '@' + key + ': ' + config.config[key] + ';';
        content += '\n';
    }

    config.less.forEach(function (item) {
        if (/(\.css)$/.test(item)) {
            // Entry is a css file and needs to be imported inline
            content += `@import (inline) "../${item}";`;
        } else {
            content += `@import "../${item}";`;
        }
    });

    grunt.file.write('../web/cache/all.less', content);

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
                    relativeUrls: true
                },
                files: lessTargetFile
            },
            development: {
                options: {
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
                    '../engine/Shopware/Plugins/**/*.css',
                    '../themes/Frontend/**/*.less',
                    '../themes/Frontend/**/*.css',
                    '../custom/plugins/**/*.less',
                    '../custom/plugins/**/*.css'
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
        eslint: {
            src: [
                'Gruntfile.js',
                '../themes/Frontend/**/_public/src/js/*.js',
                '../engine/Shopware/Plugins/**/frontend/**/src/js/**/*.js',
                '../custom/plugins/**/frontend/**/src/js/**/*.js'
            ],
            options: {
                configFile: '.eslintrc.js'
            }
        },
        fileExists: {
            js: jsFiles
        }
    });

    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-chokidar');
    grunt.loadNpmTasks('gruntify-eslint');
    grunt.loadNpmTasks('grunt-file-exists');

    grunt.renameTask('chokidar', 'watch');
    grunt.registerTask('production', [ 'eslint', 'less:production', 'uglify:production' ]);
    grunt.registerTask('default', [ 'fileExists:js', 'less:development', 'uglify:development', 'watch' ]);
};
