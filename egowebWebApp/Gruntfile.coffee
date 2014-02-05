# Grunt configuration updated to latest Grunt. That means your minimum
# version necessary to run these tasks is Grunt 0.4.
#
# Please install this locally and install `grunt-cli` globally to run.
theme="egoweb"
module.exports = (grunt) ->
    # Initialize the configuration.
    grunt.initConfig
        pkg: grunt.file.readJSON "package.json"
        sprite:
            build1:
                src: __dirname + "/themes/"+theme+"/img/sprites/*.png"
                destImg: __dirname + "/themes/"+theme+"/img/spritesheet.png"
                imgPath: '../img/spritesheet.png',
                destCSS: __dirname + "/themes/"+theme+"/less/sprites.less"
        less:
            options:
                compress: true
            build1:
                src: __dirname + "/themes/"+theme+"/less/main.less"
                dest: __dirname + "/themes/"+theme+"/css/main.css"
        coffee:
            options:
                compress: true
            build1:
                src: [__dirname + "/themes/"+theme+"/coffee/MasterClass.coffee", __dirname + "/themes/"+theme+"/coffee/classes/*.coffee"]
                dest: __dirname + "/themes/"+theme+"/js/main.js"
        uglify:
            build1:
                src: __dirname + "/themes/"+theme+"/js/main.js"
                dest: __dirname + "/themes/"+theme+"/js/main.min.js"
            build2:
                src: [__dirname + "/themes/"+theme+"/js/plugins.js", __dirname + "/themes/"+theme+"/js/plugins/*.js"]
                dest: __dirname + "/themes/"+theme+"/js/plugins.min.js"
        watch:
            myscripts:
                files:[__dirname + "/themes/"+theme+"/js/plugins/*.js", __dirname + "/themes/"+theme+"/js/plugins.js", __dirname + "/themes/"+theme+"/coffee/*.coffee", __dirname + "/themes/"+theme+"/coffee/classes/*.coffee"]
                tasks: ['myscripts']
            myless:
                files:[__dirname + "/themes/"+theme+"/less/*.less", __dirname + "/themes/"+theme+"/less/flat-ui/*.less"]
                tasks: ['less']
    # Load external Grunt task plugins.
    grunt.loadNpmTasks "grunt-spritesmith"
    grunt.loadNpmTasks "grunt-contrib-less"
    grunt.loadNpmTasks "grunt-contrib-coffee"
    grunt.loadNpmTasks "grunt-contrib-uglify"
    grunt.loadNpmTasks "grunt-contrib-watch"
    # Default task.
    grunt.registerTask "default", ["less", "coffee", "uglify"]
    grunt.registerTask "myscripts", ["coffee", "uglify"]
    grunt.registerTask "myimages", ["sprite"]
    grunt.event.on "watch", (action, filepath, target) ->
        grunt.log.writeln target + ': ' + filepath + ' has ' + action