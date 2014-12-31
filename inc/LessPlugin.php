<?php
/**
 *    Copyright 2013  Agence Ecedi  (email : contact@ecedi.fr)
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License, version 2, as
 *   published by the Free Software Foundation.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, write to the Free Software
 *   Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package Wordpress-Plugins
 * @subpackage less-compile
 * @copyright 2014 Agence Ecedi http://www.ecedi.fr
 */

/**
 * LessPlugin hook in the plugin to Wordpress core
 *
 * some conventions:
 *   * any method name finishing with Action is an action handler ex initTextdomainAction()
 *   * any method name finishing with Filter is a filter handler ex xxFilter()
 *   * public method hookIn() must be called to wired to Wordpress kernel
 *
 * @author Julien Zerbib <juz@ecedi.fr>, Sylvain Gogel <sgogle@ecedi.fr>
 * @since  2.0
 */
class LessPlugin
{
    /**
     * a less compilator
     * @var lessc
     */
    private $compilator;

    /**
     * a regex pattern to detect less files
     */
    const LESS_PATTERN =  '/\.less$/U';

    /**
     * We no more hook in Wordpress kernel in the constructor, as we may
     * require some utility functions without hooking in
     *
     * @since 2.0
     * @author  Sylvain Gogel <sgogel@ecedi.fr>
     */
    public function hookIn()
    {
        // register actions
        add_action('plugins_loaded', array( $this, 'initTextdomainAction' ));
        add_action('admin_menu', array( $this, 'adminMenuAction' ));
        add_action('wp_enqueue_scripts', array($this, 'enqueueScriptsAction'), PHP_INT_MAX, 0);
    }

    /**
     * helper function to get WP_Styles object
     *
     * @since 2.0
     * @author  Sylvain Gogel <sgogel@ecedi.fr>
     * @return WP_Styles the global wp_styles variable
     */
    private function getStyles()
    {
        global $wp_styles;

        return $wp_styles;
    }

    /**
     * action handler to compile less files on the fly
     * We fist find any enqueued .less files
     *
     * @since 2.0
     * @author  Sylvain Gogel <sgogel@ecedi.fr>
     * @todo  make sure less files are locals, we cannot compile remote less
     * @todo  je pense que la ce travail est fait en permanence, il y a p-e des optimisations à faire.
     */
    public function enqueueScriptsAction()
    {
        $styles = $this->getStyles();

        $toProcess = [];

        foreach ($styles->queue as $styleId) {
            if (preg_match(self::LESS_PATTERN, $styles->registered[$styleId]->src)) {
                $toProcess[] = $styleId;
            }
        }

        foreach ($toProcess as $handle) {
            $src = $styles->registered[$handle]->src;
            $src = preg_replace('#^'.get_theme_root_uri().'#U', '', $src);
            $path = get_theme_root().$src;
            $dest = $this->checkCompileFile($path);

            $destUri = preg_replace('#^'.$this->getCompiledLessDestination().'#U', '', $dest);
            $uploadDir = wp_upload_dir();
            $styles->registered[$handle]->src = $uploadDir['baseurl'].'/less'.$destUri;
        }
    }

    /**
     * Init textdomain Action
     * @since 2.0
     * @author  Sylvain Gogel <sgogel@ecedi.fr>
     *
     */
    public function initTextdomainAction()
    {
        load_plugin_textdomain('lc', false, dirname(plugin_basename(__FILE__)).'/../lang');
    }

    /**
     * Create an Less compile menu entry in "Tools" called "Less compile"
     * @since 1.0
     * @author  Julien Zerbib <juz@ecedi.fr>
     */
    public function adminMenuAction()
    {
        $toolsPage = add_management_page(
            __('Less', 'lc'),
            __('Less', 'lc'),
            'manage_options',
            'less_compile',
            array( $this, 'toolsPage' )
        );

        add_action('admin_print_styles-'.$toolsPage, array( &$this, 'adminPrintStylesAction' ));
    }

    /**
     * Display a tools page
     * @since 1.0
     * @author  Julien Zerbib <juz@ecedi.fr>
     */
    public function toolsPage()
    {
        $messages = array();

        if (isset($_GET[ 'action' ]) && $_GET[ 'action' ] == 'compile') {
            $messages[] = $this->compile();
        }

        // Render the tools template
        include dirname(__FILE__).'/../views/tools.php';
    }

    /**
     * Add Style to the Less Compile admin page
     *
     * @since 2.0
     * @author  Julien Zerbib <juz@ecedi.fr>
     */
    public function adminPrintStylesAction()
    {
        wp_enqueue_style('lc-css', plugins_url('less-compile').'/css/lc.css');
    }

    /**
     * Return absolut path to compiled less files, and generate the destination folder if required
     *
     * @author  Sylvain Gogel <sgogel@ecedi.fr>
     * @return string path
     * @since 2.0
     */
    protected function getCompiledLessDestination()
    {
        $uploadDir = wp_upload_dir();
        $uploadLess = $uploadDir['basedir']."/less";
        if (!is_dir($uploadLess)) {
            wp_mkdir_p($uploadLess);
        }

        return $uploadLess;
    }

    /**
     * Find the destination file from a src, without the actual compilation
     * @param  string $src a less file path
     * @return string path to the compiled css file
     *
     * @since 2.0
     * @author  Sylvain Gogel <sgogel@ecedi.fr>
     */
    protected function getCompiledFileDestination($src)
    {
        $dest = $this->getCompiledLessDestination();

        $ext = pathinfo($src, PATHINFO_EXTENSION);
        $fileName = pathinfo($src, PATHINFO_BASENAME);

        // on ne traite pas le fichier si celui-ci possède un espace dans son nom (merci Windows)
        if ($ext == 'less' && strpos($src, ' ') === false) {
            return $dest.'/'.$fileName.'.css';
        }

        throw new Exception('Cannot generate .less destination');
    }

    /**
     * singleton method to get a lessc compilator
     *
     * @return lessc a lessc instance from https://github.com/leafo/lessphp
     * @see  https://github.com/leafo/lessphp
     * @since 2.0
     * @author  Sylvain Gogel <sgogel@ecedi.fr>
     */
    private function getCompilator()
    {
        if (is_object($this->compilator)) {
            return $this->compilator;
        }

        $less = new lessc();
        $less->setFormatter('compressed');

        $this->compilator = $less;

        return $this->compilator;
    }

    /**
     * Compile a less File
     *
     * @param  string $src path to a .less file
     * @return string path to the compiled version
     *
     * @throws Exception If less file cannot be compiled
     * @since 2.0
     * @author  Sylvain Gogel <sgogel@ecedi.fr>
     */
    public function compileFile($src)
    {
        $dest = $this->getCompiledFileDestination($src);

        $less = $this->getCompilator();

        try {
            $less->compileFile($src, $dest);

            return $dest;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Check if file required Compilation, and do it if needed
     *
     * @param  string $src path to a .less file
     * @return string path to the compiled version
     *
     * @throws Exception If less file cannot be compiled
     * @since 2.0
     * @author  Sylvain Gogel <sgogel@ecedi.fr>
     */
    public function checkCompileFile($src)
    {
        $dest = $this->getCompiledFileDestination($src);

        $less = $this->getCompilator();

        try {
            $less->checkedCompile($src, $dest);

            return $dest;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Compile all less files in theme stylesheet directory
     * @since 2.0
     * @author  Sylvain Gogel <sgogel@ecedi.fr>
     * @todo pouvoir forcer la compilation depuis le back office
     */
    public function compile()
    {
        $cssPath = get_stylesheet_directory().'/css';
        $message = __('Compiled files in: ', 'lc').'<span class="stylepath">'.$cssPath.'</span><br /><br /><ul>';

        $dirHandler  = opendir($cssPath);

        while (false !== ($filename = readdir($dirHandler))) {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);

            if ($ext === 'less') {
                try {
                    $dest = $this->compileFile($cssPath.'/'.$filename);
                    $message .= '<li><span class="lessfile">'.$filename.'</span>'.__(' compiled in ', 'lc').'<span class="cssfile">'.$dest.'</span></li>';
                } catch (exception $e) {
                    $message .= '<li>fatal error: '.$e->getMessage().'</li>';
                }
            }
        }

        $message .= '</ul>';

        return $message;
    }
}
