<?php
/*
Plugin Name: Wp Less Compile
Plugin URI: http://www.ecedi.fr/
Description: Compile all Less files in Theme Stylesheet Directory
Version: 2.2
Author: Julien Zerbib, Sylvain Gogel <sgogel@ecedi.fr>
Author URI: http://www.ecedi.fr/


    Copyright 2013  Agence Ecedi  (email : contact@ecedi.fr)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * The plugin requires leafo/lessphp from https://github.com/leafo/lessphp
 * for conveniancy we embed it here
 * @see https://github.com/leafo/lessphp
 */
require_once __DIR__.'/inc/LessPlugin.php';
require_once __DIR__.'/inc/lessc.inc.php';

$lessPlugin = new LessPlugin();
$lessPlugin->hookIn();
