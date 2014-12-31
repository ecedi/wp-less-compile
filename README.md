# Less Compile a plugin to bring less preprocessor to Wordpress

by [Agence Ecedi](http://ecedi.fr)

## Installation

### prerequis

  * php 5.4.\* or Up (tested on PHP 5.5.17)
  * Wordpress 4.01 or more (should work on older versions)

## Usage

Just enable the plugin, it should do the work:
  - Find all enqueued .less files and compile them when needed
  - From Admin you can force compilation of active theme .less (beta)

To enqueue a .less file, do as usual for any styles

```
  wp_enqueue_style('my-less', get_stylesheet_directory().'/styles.less', array(), 'v1.0');