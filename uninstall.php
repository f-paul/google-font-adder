<?// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// delete all the saved option in the bdd
delete_option('gfont_name');
delete_option('gfont_url');