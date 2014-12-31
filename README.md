# Less Compile a plugin to bring less preprocessor to Wordpress

by [Agence Ecedi](http://ecedi.fr)

## Installation

### prerequis

    * php 5.4.\* or Up (tested on PHP 5.5.17)
    * Wordpress 4.01 or more (should work on older versions)

### From Github

Go to github [Agence Ecedi](https://github.com/ecedi/wp-less-compile) and download master or latest tag archive

ex: [wp-less-compile.master.zip](https://github.com/ecedi/wp-less-compile/archive/master.zip)

And extract it to your wp-content/plugins folder and rename the folder less-compile

### With Composer

In your composer.json file add the following

```json
    {
        "require": {
            "ecedi/wp-less-compile": "dev-master",
        },
        "repositories": [
            {
                "type": "vcs",
                "url": "https://github.com/ecedi/wp-less-compile"
            }
        ]
    }
```

then run
```
composer update
`̀``


## Usage

Just enable the plugin, it should do the work:
    - Find all enqueued .less files and compile them when needed
    - From Admin you can force compilation of active theme .less (beta)

To enqueue a .less file, do as usual for any styles

```
    wp_enqueue_style('my-less', get_stylesheet_directory().'/styles.less', array(), 'v1.0');
```
