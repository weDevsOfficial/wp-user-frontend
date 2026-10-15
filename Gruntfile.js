'use strict';
module.exports = function( grunt) {
    const tailwindFileMap = {
        'templates/account.php': 'frontend/account.css',
    }

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
                    '<%= dirs.css %>/frontend-forms.css': '<%= dirs.less %>/frontend-forms.less',
                    '<%= dirs.css %>/elementor-frontend-forms.css': '<%= dirs.less %>/elementor-frontend-forms.less'
                }
            },

            admin: {
                files: {
                    // wpuf-form-builder.css is plain CSS now (tools/admin-css/src/legacy/, task 5.1b).
                    '<%= dirs.css %>/admin.css': ['<%= dirs.less %>/admin.less']
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
                    exclude: ['build/.*', 'node_modules/*', 'plugins/.*', 'tests/.*', 'vendor/.*'],
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
                    'src/admin/apps/form-builder/**/*.{js,jsx}',
                ],
                tasks: [
                    'shell:npm_build_form_builder'
                ]
            },

            aiFormBuilder: {
                files: [
                    'src/admin/apps/ai-form-builder/**/*.{js,jsx}',
                    'tools/admin-css/src/ai-form-builder.css',
                ],
                tasks: [
                    'shell:npm_build_ai_form_builder'
                ]
            },

            tailwind: {
                files: [
                    'src/css/**/*.css',
                    'admin/form-builder/views/*.php',
                    'src/admin/apps/form-builder/**/*.{js,jsx}',
                    'includes/Admin/**/*.php',
                    'templates/**/*.php',
                    'includes/Free/Free_Loader.php',
                    'wpuf-functions.php',
                ],
                tasks: ['tailwind'],
                options: {
                    spawn: false
                }
            },


            userDirectory: {
                files: [
                    'src/js/user-directory/**/*.js',
                    'src/js/user-directory/**/*.jsx',
                    'src/js/user-directory/**/*.css',
                    'src/js/user-directory/styles/*.css',
                    'modules/user-directory/**/*.php',
                ],
                tasks: ['build-user-directory'],
                options: {
                    spawn: false
                }
            },

        },

        // is to run NPM commands through Grunt
        shell: {
            // POT with the React strings (WP-CLI make-pot over src/admin and the
            // assets/js/screens bundles); grunt-wp-i18n reads PHP only.
            makepot: {
                command: 'node bin/make-pot.mjs',
            },
            npm_build_ai_form_builder: {
                command: 'pnpm run build:ai-form-builder',
            },
            npm_build_form_builder: {
                command: 'pnpm run build:form-builder',
            },
            npm_build_user_directory: {
                command: 'pnpm run build:user-directory',
            },
            tailwind: {
                command: function ( input, output ) {
                    return `npx tailwindcss -i ${input} -o ${output} --minify`;
                }
            },
            tailwind_minify: {
                command: function ( input, output ) {
                    return `npx tailwindcss -i ${input} -o ${output} --minify`;
                }
            }
        }
    });

    // Load NPM tasks to be used here
    grunt.loadNpmTasks( 'grunt-contrib-less' );
    grunt.loadNpmTasks( 'grunt-wp-i18n' );
    grunt.loadNpmTasks( 'grunt-contrib-uglify' );
    grunt.loadNpmTasks( 'grunt-contrib-watch' );
    // grunt.loadNpmTasks( 'grunt-notify' );
    grunt.loadNpmTasks( 'grunt-wp-readme-to-markdown' );
    grunt.loadNpmTasks( 'grunt-shell' );
    grunt.loadNpmTasks( 'grunt-postcss' );

    grunt.registerTask( 'default', [ 'less', 'uglify', 'i18n', 'tailwind' ] );

    // file auto generation
    grunt.registerTask( 'i18n', [ 'shell:makepot' ] );
    grunt.registerTask( 'readme', [ 'wp_readme_to_markdown' ] );

    // build stuff
    // No release task here: `pnpm release` is the one release command (build once,
    // POT, zip through bin/zip.mjs).

    // User Directory CSS build task
    grunt.registerTask( 'build-user-directory', 'Build User Directory CSS with Tailwind', function() {
        grunt.task.run('shell:npm_build_user_directory');
    });

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

    grunt.registerTask('tailwind-minify', function() {
        const cssFiles = [
            { input: 'assets/css/frontend-subscriptions.css', output: 'assets/css/frontend-subscriptions.min.css' }
        ];

        cssFiles.forEach(file => {
            if (grunt.file.exists(file.input)) {
                grunt.task.run(`shell:tailwind_minify:${file.input}:${file.output}`);
            }
        });
    });
};
