<?php if (!defined('ABSPATH')) die;
/*
Plugin Name: Add any Google Font to text editor
Plugin URI: https://paulfermey.fr/
Description: Add the Google font link of your choice and it will be automatically added in your text editor.
Author: Paul FERMEY for Rouen Webmaster
Author URI: https://rouen-webmaster.com
Version: 1.0
License: GPLv2 or later
Text Domain: gfont-adder
Domain Path: /languages
*/

/* 
    If you have any issue with the plugin contact : contact@paulfermey.fr
    Thank you for using Google Font adder
*/

class gfont_adder_plugin
{
	public function __construct()
	{
        // Hook into the admin menu
		add_action( 'admin_menu', array( $this, 'create_plugin_settings_page' ) );
		add_option( 'gfont_name' );
		add_option( 'gfont_url');
		// pass the argument true to reset 
		reset_gfont(false);
    }

	public function create_plugin_settings_page()
	{
        // Add the menu item and page
        $page_title = __("Add any GFont to Editor", 'gfont-adder');
        $menu_title = __("Add Google Font", 'gfont-adder');
        $capability = 'manage_options';
        $slug = 'gfont-adder';
        $callback = array( $this, 'plugin_settings_page_content' );
        $icon = 'dashicons-editor-textcolor';
        $position = 100;
    
        add_menu_page( $page_title, $menu_title, $capability, $slug, $callback, $icon, $position );
	}

	public function plugin_settings_page_content()
	{
		// get the font url from the request $_POST
		$font_url = $_POST['url'];
		// get the removed font from the request $_POST
		$removed_font = $_POST['removed_font'];

		// Start HTML
		ob_start();
		?>
		<head>
			<link href="https://pagecdn.io/lib/easyfonts/fonts.css" rel="stylesheet" />
			<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous"/>
		</head>
		<body style="background-color:#f1f1f1">
			<h2><?= __("Google Font Control Panel", 'gfont-adder') ?></h2>
			<p></p>
			<hr>
			<!--- in this form we can get all the data we need like the url -->
			<form method="POST">
				<div class="row">
					<div class="col-md-4">
						<p style="font-size:18px; padding-top:5px"><b><?= __("Add Google Font URL  :",'gfont-adder') ?></b></p>
					</div>
					<div class="col-md-6">
						<label style="padding-top:5px"><input type="text" name="url" placeholder="<?= __("Put URL",'gfont-adder') ?>" value="<?= esc_html(empty($_POST['url'])?'':$_POST['url']) ?>"></label>
						<p style="font-size:12px"> <?= __("Don't know where you can find the url ?", 'gfont-adder') ?> <a href="https://fonts.google.com/" target="_blank"><?= __("Click here", 'gfont-adder') ?></a> <?= __("and follow" , 'gfont-adder') ?><a href="https://imgur.com/PzyGTif" target="_blank"> <?= __("this tutorial",'gfont-adder') ?></a></p>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<p style="font-size:18px; padding-top:5px"><b><?= __("Remove font  :",'gfont-adder') ?></b></p>
					</div>
					<div class="col-md-6">
						<select name="removed_font">
							<option selected="selected" value="none">---</option>
							<?php list_font_to_rm()?>
						</select>
						<p style="font-size:12px"><?= __("Select the font you want to delete, leave blank to not delete anything",'gfont-adder') ?></p>
					</div>
				</div>
				<?php
				list_font();
				submit_button();
				?>
			</form>
		</body>
		<?php
		// end HTML
		echo ob_get_clean();
		// here we check if a font as been selected in the remove wrapper
		if (strcmp($removed_font, "none") && strlen($removed_font))
			remove_font($removed_font);
		// here we check if a font already exist
		// return 0 if it's correct 1 if not
		check_if_url_already_exist($font_url);
		if (check_if_url_is_correct($font_url))
			exit();
		add_font_links($font_url);
		add_font_name($font_url);
		// reload page to display the added font
		echo '<script>window.location.reload();</script>';
	}
}
new gfont_adder_plugin();

// the purpose of this function is to reset all the gfont. Pass true or 1 to reset 
function reset_gfont($args)
{
	if ($args) {
		delete_option( 'gfont_url');
		delete_option( 'gfont_name');
		exit();
	}
}

// the purpose of this function is to check if the font was already added
function check_if_url_already_exist($font_url)
{
	// we will retrieve the content of the string contained in the bdd
	// we split the content of the string from the '|' character and create an array
	$url_list = explode('|', get_option('gfont_url'));

	// loop into the font list to check if it's already added
	for ($i = 1; $url_list[$i]; $i++)
		if (!strcmp($url_list[$i], $font_url))
			exit ('<p style="color:red">' . __("Font already added", 'gfont-adder') . '</p>');
}

// the purpose of this function is to check if the url is a google font url
function check_if_url_is_correct($font_url)
{
	if (!strncmp($font_url, 'https://fonts.googleapis.com/css2?family=', 41) && !strncmp(strrev($font_url), 'paws=yalpsid&', 13))
		echo '<p style="color:green">' . __("Font was added", 'gfont-adder') . '</p>';
	else if ($font_url != '') {
		echo '<p style="color:red">' . __("Enter a valid URL (ex : https://fonts.googleapis.com/css2?family=Piazzolla&display=swap)", 'gfont-adder') . '</p>' ;
		return (2);
	} else
		return (1);
	return (0);
}

// the purpose of this function is to be able to list all fonts in order to be display
function list_font()
{
	// we will retrieve the content of the string contained in the bdd
	// we split the content of the string from the '|' character and create an array
	$font_name = explode('|', get_option('gfont_name'));
	$count = 0;

	// display text with a counter of font already added
	for ($i = 1; $font_name[$i]; $i++, $count++);
	if (!$count)
		return;
	echo '<hr><p>'. __("Font added :", 'gfont-adder') . ' (';
	echo $count;
	echo ')</p>';
	echo '<table>';
	// loop to display each fontname
	for ($i = 1; $font_name[$i]; $i++) {
		echo '<td style="font-family:';
		echo $font_name[$i];
		echo ';padding:5px">';
		echo $font_name[$i];
		echo '</td>';
	}
	echo '</table><hr>';
}

// the purpose of this function is to be able to list all fonts in the remove wrapper
function list_font_to_rm()
{
	$font_name = explode('|', get_option('gfont_name'));

	// loop to display each fontname in the dropdown menu
	for ($i = 1; $font_name[$i]; $i++) {
		echo '<option value="';
		echo $font_name[$i];
		echo '">';
		echo $font_name[$i];
		echo '</option>';
	}
}

// the purpose of this function is to be able to remove a font
function remove_font($removed_font)
{
	// we will retrieve the content of the string contained in the bdd
	// we split the content of the string from the '|' character and create an array
	$font_name = explode('|', get_option('gfont_name'));
	$font_url = explode('|', get_option('gfont_url'));
	// here we recreate the link of the google font 
	// so that we can then remove it from the table containing all the links
	$full_url = "https://fonts.googleapis.com/css2?family=". $removed_font . "&display=swap";
	$name_str = "";
	$url_str = "";

	// we loop into the fonturl and change the character ' ' by a '+' (the space is replaced by a + in the url)
	for ($i = 0; $full_url[$i]; $i++)
		if ($full_url[$i] == ' ')
			$full_url[$i] = '+';
	// we search into the array the case associated to the font and
	// we remove it using the function unset the font in each of the tables (name and url)
	unset($font_name[array_search($removed_font, $font_name)]);
	unset($font_url[array_search($full_url, $font_url)]);
	$font_name = array_merge($font_name);
	$font_url = array_merge($font_url);

	// we loop into the font_name in order to re-create the string from the array (|name1|name2|name3|...|)
	for ($i = 1; $font_name[$i] != NULL; $i++) {
		$name_str = $name_str . '|' . $font_name[$i];
		$url_str = $url_str . '|' . $font_url[$i];
	}
	// we update the all the name into the bdd
	update_option('gfont_name', $name_str);
	update_option('gfont_url', $url_str);
	// reload the page in order to rm the font in the font list
	echo '<script>window.location.reload();</script>';
}

// the purpose of this function is to be able to add the new font urls after the existing one.
function add_font_links($font_url)
{
	// we retrieve all the urls contained in the database 
	$links = get_option('gfont_url');

	// we add the url of the new font
	$links =  $links . '|' . $font_url;
	// we update the list of the new font in the database
	update_option( 'gfont_url', $links);
}

// the purpose of this function is to be able to add the new font name after the existing one.
function add_font_name($font_url)
{
	// we retrieve all the name contained in the database 
	$name = get_option('gfont_name');
	// we get from the url the fontname who is located between the character '=' and '&'
	$font_name = trim(strstr(strstr($font_url, '='), '&', true), '=&');
	
	// we loop into the fontname and change the character '+' by a ' ' (the space is replaced by a + in the url)
 	for ($i = 0; $font_name[$i]; $i++)
		if ($font_name[$i] == '+')
			$font_name[$i] = ' ';
	$name = $name . '|' . $font_name;
	// we update the all the name into the bdd
	update_option('gfont_name', $name);
}

// the purpose of this function is to add the font to the css by adding the link to the style sheet
function add_google_webfonts_to_editor() {
	// we will retrieve the content of the string contained in the bdd
	// we split the content of the string from the '|' character and create an array
	$font_url = explode('|', get_option('gfont_url'));

	// think it's explicit, wondering why starting at 1 ?
	// It's because the first box of the table is just a '|'
	for ($i = 1; $font_url[$i]; $i++)
		add_editor_style( str_replace( ',', '%2C', $font_url[$i]) );
}
add_action( 'init', 'add_google_webfonts_to_editor' );

// the purpose of this function is to add the font into the list of font in your editor
function add_custom_font_list($array) {
	// same technic than below to get the list of the added font
	$additionnal_font = explode('|', get_option('gfont_name'));
	// here is the list of the basic font of wordpress
	$font_formats = "Andale Mono=andale mono,times;" .
					"Arial=arial,helvetica,sans-serif;" .
					"Arial Black=arial black,avant garde;" .
					"Book Antiqua=book antiqua,palatino;" .
					"Comic Sans MS=comic sans ms,sans-serif;" .
					"Courier New=courier new,courier;" .
					"Georgia=georgia,palatino;" .
					"Helvetica=helvetica;" .
					"Impact=impact,chicago;" .
					"Symbol=symbol;" .
					"Tahoma=tahoma,arial,helvetica,sans-serif;" .
					"Terminal=terminal,monaco;" .
					"Times New Roman=times new roman,times;" .
					"Trebuchet MS=trebuchet ms,geneva;" .
					"Verdana=verdana,geneva;" .
					"Webdings=webdings;" .
					"Wingdings=wingdings,zapf dingbats;";
	// we loop to add all the fontname after the list of the basic font of wordpress
	for ($i = 1; $additionnal_font[$i]; $i++)
		$font_formats = $font_formats . $additionnal_font[$i] . '=' . $additionnal_font[$i] . ';' ;
	$array['font_formats']= $font_formats;
 
 	return $array;
}
add_filter('tiny_mce_before_init', 'add_custom_font_list' );

// the purpose of this function is to load the languages
function my_theme_load_theme_textdomain() {
    load_plugin_textdomain('gfont-adder', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action( 'after_setup_theme', 'my_theme_load_theme_textdomain' );