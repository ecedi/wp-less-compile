<?php
/**
 * Display a form to compile less files
 * @package Wordpress-Plugins
 * @subpackage less-compile
 * @copyright 2014 Agence Ecedi http://www.ecedi.fr*
 */
?>
    <h1><?php _e('Less Compile', 'lc') ?></h1>
<?php

if ($messages != array()) :
    ?>
        <div id="message" class="compiled">
            <?php
                foreach ($messages as $message) {
                    print '<p>'.$message.'</p>';
                }
            ?>
        </div>
    <?php
endif;
?>
<form method="GET" action="<?php print esc_url('tools.php'); ?>">
    <input type="hidden" name="page" value="less_compile" />
    <input type="hidden" name="action" value="compile" />
    <input class="button-primary button" value="<?php _e('Compile less files', 'lc'); ?>" type="submit" />
</form>
