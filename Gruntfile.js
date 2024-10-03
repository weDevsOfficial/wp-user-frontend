'use strict';
module.exports = function(grunt) {
    var formBuilderAssets = require('./admin/form-builder/assets/js/form-builder-assets.js');
    var pkg = grunt.file.readJSON('package.json');

    grunt.initConfig({
        // setting folder templates
        dirs: {
            css: 'assets/css',
            less: 'assets/less',
            images: 'assets/images',
            js: 'assets/js',
            template: 'assets/js-templates'
        },

        // Compile all .less files.
        less: {
            front: {
                files: {
                    '<%= dirs.css %>/frontend-forms.css': '<%= dirs.less %>/frontend-forms.less'
                }
            },
            admin: {
                files: {
                    '<%= dirs.css %>/wpuf-form-builder.css': ['admin/form-builder/assets/less/form-builder.less'],
                    '<%= dirs.css %>/admin.css': ['<%= dirs.less %>/admin.less'],
                    '<%= dirs.css %>/admin/whats-new.css': ['<%= dirs.less %>/whats-new.less'],
                    '<%= dirs.css %>/registration-forms.css': ['<%= dirs.less %>/registration-forms.less']
                }
            }
        },

        wp_readme_to_markdown: {
            wpuf: {
                files: {
                    'readme.md': 'readme.txt'
                }
            },
        },

        addtextdomain: {
            options: {
                textdomain: 'wp-user-frontend',
            },
            update_all_domains: {
                options: {
                    updateDomains: true
                },
                src: [ '*.php', '**/*.php', '!node_modules/**', '!php-tests/**', '!bin/**', '!build/**', '!assets/**' ]
            }
        },

        // Generate POT files.
        makepot: {
            target: {
                options: {
                    exclude: ['build/.*', 'node_modules/*'],
                    mainFile: 'wpuf.php',
                    domainPath: '/languages/',
                    potFilename: 'wp-user-frontend.pot',
                    type: 'wp-plugin',
                    updateTimestamp: true,
                    potHeaders: {
                        'report-msgid-bugs-to': 'https://wedevs.com/contact/',
                        'language-team': 'LANGUAGE <EMAIL@ADDRESS>',
                        poedit: true,
                        'x-poedit-keywordslist': true
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
            options: {
                debounceDelay: 500, // Add debounce delay
                spawn: false // Recommended for better performance
            },
            less: {
                files: ['<%= dirs.less %>/*.less'],
                tasks: ['less:front', 'less:admin']
            },
            formBuilder: {
                files: [
                    'admin/form-builder/assets/less/*',
                    'admin/form-builder/assets/js/**/*',
                    'assets/js/wpuf-form-builder-wpuf-forms.js',
                    '<%= dirs.css %>/frontend-forms.less',
                ],
                tasks: [
                    'jshint:formBuilder', 'less:admin',
                    'concat:formBuilder', 'concat:templates', 'less:front'
                ]
            },
            vue: {
                files: [
                    'assets/js/**/*.{js,vue}',
                    'src/js/**/*.{js,vue}',
                    '!assets/js/**/*.min.js', // Exclude minified files
                    '!src/js/**/*.min.js'     // Exclude minified files
                ],
                tasks: ['shell:npm_build'],
                options: {
                    debounceDelay: 1000, // Longer delay for Vue files
                    spawn: false,
                    interval: 1000 // Add interval
                }
            }
        },

        // Shell command for npm build
        shell: {
            npm_build: {
                command: 'npm run build',
                options: {
                    stdout: true,
                    stderr: true
                }
            }
        }
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
    grunt.loadNpmTasks( 'grunt-wp-readme-to-markdown' );
    grunt.loadNpmTasks( 'grunt-shell' );

    // Task optimization
    let changedFiles = Object.create(null);
    let onChange = grunt.util._.debounce(function() {
        grunt.config('shell.npm_build.src', Object.keys(changedFiles));
        changedFiles = Object.create(null);
    }, 200);

    grunt.event.on('watch', function(action, filepath) {
        changedFiles[filepath] = action;
        onChange();
    });

    // Define tasks
    grunt.registerTask( 'default', ['less', 'concat', 'uglify', 'i18n'] );
    grunt.registerTask( 'i18n', ['makepot'] );
    grunt.registerTask( 'readme', ['wp_readme_to_markdown'] );
    grunt.registerTask( 'release', ['less', 'concat', 'uglify', 'i18n', 'readme'] );
    grunt.registerTask( 'zip', ['clean', 'copy', 'compress'] );
};
