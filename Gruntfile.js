'use strict';
module.exports = function(grunt) {
    var formBuilderAssets = require('./admin/form-builder/assets/js/form-builder-assets.js');

    var pkg = grunt.file.readJSON('package.json');

    grunt.initConfig({
        // setting folder templates
        dirs: {
            css: 'assets/css',
            images: 'assets/images',
            js: 'assets/js',
            template: 'assets/js-templates'
        },

        // Compile all .less files.
        less: {

            // one to one
            front: {
                files: {
                    '<%= dirs.css %>/frontend-forms.css': '<%= dirs.css %>/frontend-forms.less'
                }
            },

            admin: {
                files: {
                    '<%= dirs.css %>/formbuilder.css': ['<%= dirs.css %>/formbuilder.less']
                }
            },

            formBuilder: {
                files: {
                    '<%= dirs.css %>/wpuf-form-builder.css': ['admin/form-builder/assets/less/form-builder.less']
                }
            }
        },

        // Generate POT files.
        makepot: {
            target: {
                options: {
                    exclude: ['build/.*'],
                    domainPath: '/languages/', // Where to save the POT file.
                    potFilename: 'wpuf.pot', // Name of the POT file.
                    type: 'wp-plugin', // Type of project (wp-plugin or wp-theme).
                    potHeaders: {
                        'report-msgid-bugs-to': 'https://wedevs.com/support/forum/plugin-support/wp-user-frontend/',
                        'language-team': 'LANGUAGE <EMAIL@ADDRESS>'
                    }
                }
            }
        },

        uglify: {
            minify: {
                files: {
                    '<%= dirs.js %>/frontend-form.min.js': ['<%= dirs.js %>/frontend-form.js'],
                    '<%= dirs.js %>/upload.min.js': ['<%= dirs.js %>/upload.js'],
                }
            }
        },

        watch: {
            less: {
                files: ['<%= dirs.css %>/*.less'],
                tasks: ['less:front', 'less:admin'],
                options: {
                    livereload: true
                }
            },

            formBuilder: {
                files: [
                    'admin/form-builder/assets/less/*',
                    'admin/form-builder/assets/js/**/*',
                    'assets/js/wpuf-form-builder-wpuf-forms.js',
                    '<%= dirs.css %>/frontend-forms.less',
                ],
                tasks: [
                    'jshint:formBuilder', 'less:formBuilder',
                    'concat:formBuilder', 'concat:templates', 'less:front'
                ]
            }
        },

        // Clean up build directory
        clean: {
            main: ['build/']
        },

        // Copy the plugin into the build directory
        copy: {
            main: {
                src: [
                    '**',
                    '!node_modules/**',
                    '!build/**',
                    '!admin/form-builder/assets/**',
                    '!assets/css/*.less',
                    '!bin/**',
                    '!.git/**',
                    '!includes/pro/.git/**',
                    '!Gruntfile.js',
                    '!secret.json',
                    '!package.json',
                    '!debug.log',
                    '!phpunit.xml',
                    '!.gitignore',
                    '!.gitmodules',
                    '!npm-debug.log',
                    '!plugin-deploy.sh',
                    '!export.sh',
                    '!config.codekit',
                    '!**/nbproject/**',
                    '!assets/less/**',
                    '!tests/**',
                    '!**/Gruntfile.js',
                    '!**/package.json',
                    '!**/readme.md',
                    '!**/docs.md',
                    '!**/*~'
                ],
                dest: 'build/'
            }
        },

        //Compress build directory into <name>.zip and <name>-<version>.zip
        compress: {
            main: {
                options: {
                    mode: 'zip',
                    archive: './build/wp-user-frontend.zip'
                },
                expand: true,
                cwd: 'build/',
                src: ['**/*'],
                dest: 'wp-user-frontend'
            }
        },

        // jshint
        jshint: {
            options: {
                jshintrc: '.jshintrc',
                reporter: require('jshint-stylish')
            },

            formBuilder: [
                'admin/form-builder/assets/js/**/*.js',
                '!admin/form-builder/assets/js/jquery-siaf-start.js',
                '!admin/form-builder/assets/js/jquery-siaf-end.js',
                'assets/js/wpuf-form-builder-wpuf-forms.js',
            ]
        },

        // concat/join files
        concat: {
            formBuilder: {
                files: {
                    '<%= dirs.js %>/wpuf-form-builder.js': 'admin/form-builder/assets/js/form-builder.js',
                    '<%= dirs.js %>/wpuf-form-builder-mixins.js': formBuilderAssets.mixins,
                    '<%= dirs.js %>/wpuf-form-builder-components.js': formBuilderAssets.components,
                },
            },

            templates: {
                options: {
                    process: function(src, filepath) {
                        var id = filepath.replace('/template.php', '').split('/').pop();

                        return '<script type="text/x-template" id="tmpl-wpuf-' + id + '">\n' + src + '</script>\n';
                    }
                },
                files: {
                    '<%= dirs.template %>/form-components.php': formBuilderAssets.componentTemplates,
                }
            }
        },
    });

    // Load NPM tasks to be used here
    grunt.loadNpmTasks( 'grunt-contrib-less' );
    grunt.loadNpmTasks( 'grunt-contrib-concat' );
    grunt.loadNpmTasks( 'grunt-contrib-jshint' );
    grunt.loadNpmTasks( 'grunt-wp-i18n' );
    grunt.loadNpmTasks( 'grunt-contrib-uglify' );
    grunt.loadNpmTasks( 'grunt-contrib-watch' );
    grunt.loadNpmTasks( 'grunt-contrib-clean' );
    grunt.loadNpmTasks( 'grunt-contrib-copy' );
    grunt.loadNpmTasks( 'grunt-contrib-compress' );
    grunt.loadNpmTasks( 'grunt-notify' );

    grunt.registerTask( 'default', [
        'makepot', 'uglify'
    ]);

    grunt.registerTask( 'zip', [
        'clean', 'copy', 'compress'
    ]);
};
