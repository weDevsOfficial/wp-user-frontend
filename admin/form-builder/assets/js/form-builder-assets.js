/**
 * Returns file paths of vue assets
 */

/* global module, require */
function assets() {
    'use strict';

    const grunt     = require('grunt');
    const fs        = require('fs');
    let paths       = ['admin/form-builder/assets/js/jquery-siaf-start.js'];

    // mixins
    const mixinsPath  = './admin/form-builder/assets/js/mixins/';
    let mixins        = fs.readdirSync(mixinsPath);

    mixins.forEach((mixin) => {
        const path = `${mixinsPath}${mixin}`;

        if (grunt.file.isFile(path)) {
            paths.push(path);
        }
    });

    // components
    const componentPath  = './admin/form-builder/assets/js/components/';
    let components       = fs.readdirSync(componentPath);

    components.forEach((component) => {
        const path = `${componentPath}${component}`;

        if (grunt.file.isDir(path)) {
            paths.push(path + '/index.js');
        }
    });

    paths.push('admin/form-builder/assets/js/form-builder.js');
    paths.push('admin/form-builder/assets/js/jquery-siaf-end.js');

    return paths;
}

module.exports = assets();
