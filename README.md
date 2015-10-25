# asset-manager
Library to combine and mangle your assets and version them.
For now only JavaScript is supported.

### Installation


Following scripts have to be executed

```json
  "post-autoload-dump": [
       "cd vendor/mishoo/UglifyJS2 && npm install",
       "ln -sf `pwd`/vendor/mishoo/UglifyJS2/bin/uglifyjs `pwd`/vendor/jandc/uglify-js-wrapper/bin/uglifyjs",
       "chmod +x `pwd`/vendor/jandc/uglify-js-wrapper/bin/uglifyjs"
     ]
```
```


If you encounter difficulties resolving the javascript dependencies, add following custom repositories:

```json
"repositories": [
    {
      "type": "package",
      "package": {
        "name": "mishoo/UglifyJS2",
        "version": "2.4.24",
        "source": {
          "type": "git",
          "url": "https://github.com/mishoo/UglifyJS2",
          "reference": "tags/v2.4.24"
        }
      }
    }    
  ]
```


### Usage

You can use the assetManager class out of the box, or register the Twig Extension to your twig environment.

Notably you can use the 'manglejs'/'endmanglejs' tags to encapsulate inline javascript in your template to mangle and minify them 
