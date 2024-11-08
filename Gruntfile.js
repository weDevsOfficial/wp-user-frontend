'use strict';
module.exports = function( grunt) {
    const tailwindFileMap = {
        'admin/form-builder/views/form-builder.php': 'admin/form-builder.css',
    }

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

            // one to one
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
                    'assets/js/subscriptions.js',
                    'assets/css/admin/subscriptions.css',
                    'assets/js/components/**/*.vue',
                    'assets/js/stores/**/*.js',
                ],
                tasks: [
                    'shell:npm_build'
                ]
            },

            tailwind: {
                files: [
                    'src/css/**/*.css',
                    'admin/form-builder/views/*.php',
                    'includes/Admin/views/*.php',
                    'admin/form-builder/assets/js/**/*'
                ],
                tasks: ['shell:tailwind'],
                options: {
                    spawn: false
                }
            },
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
                    '!assets/tailwind/**',
                    '!tests/**',
                    '!**/Gruntfile.js',
                    '!**/package.json',
                    '!**/readme.md',
                    '!**/docs.md',
                    '!**/*~',
                    '!**/log.txt',
                    '!**/package-lock.json',
                    '!**/appsero.json',
                    '!**/composer.json',
                    '!**/composer.lock',
                    '!**/phpcs-report.txt',
                    '!**/phpcs.xml.dist'
                ],
                dest: 'build/'
            }
        },

        //Compress build directory into <name>.zip and <name>-<version>.zip
        compress: {
            main: {
                options: {
                    mode: 'zip',
                    archive: './build/wp-user-frontend-v'+pkg.version+'.zip'
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

        // is to run NPM commands through Grunt
        shell: {
            npm_build: {
                command: 'npm run build',
            },
            tailwind: {
                command: function ( input, output ) {
                    return `npx tailwindcss -i ${input} -o ${output}`;
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
    grunt.loadNpmTasks( 'grunt-postcss' );

    grunt.registerTask( 'default', [ 'less', 'concat', 'uglify', 'i18n' ] );

    // file auto generation
    grunt.registerTask( 'i18n', [ 'makepot' ] );
    grunt.registerTask( 'readme', [ 'wp_readme_to_markdown' ] );

    // build stuff
    grunt.registerTask( 'release', [ 'less', 'concat', 'uglify', 'i18n', 'readme' ] );
    grunt.registerTask( 'zip', [ 'clean', 'copy', 'compress' ] );

    grunt.event.on('watch', function(action, filepath, target) {
        if (target === 'tailwind') {
            grunt.task.run('tailwind');
        }
    });

    grunt.registerTask('tailwind', function() {
        const done = this.async();

        // Process each file mapping
        Object.entries(tailwindFileMap).forEach(([phpFile, cssFile]) => {
            const inputFile = `src/css/${cssFile}`;
            const outputFile = `assets/css/${cssFile}`;

            // Ensure the input file exists
            if (grunt.file.exists(inputFile)) {
                // Run the tailwind command
                grunt.task.run(`shell:tailwind:${inputFile}:${outputFile}`);
            }
        });

        done();
    });
};
