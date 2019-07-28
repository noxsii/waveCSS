<?php
/**
 * Copyright (c) 2019.
 *
 * noxsi _WAVE.
 * by noxsi.io
 * made in Stuttgart - Germany
 * develope by Dennis Schirra
 *
 * @link   https://noxsi.io
 * @contact dennis@noxsi.io
 *
 * @file   : waveCSS.php
 * @project: waveCSS
 * @module : noxsi.io
 * @last   edit: 28.07.2019 14:20
 * @user   inkmu
 *
 */
/*
 * Plugin Name:       noxsi waveCSS
 * Plugin URI:        https://noxsi.io/wavecss
 * Description:       Custom CSS for Pages and Posts
 * Version:           1.0
 * Requires PHP:      7.2
 * Author:            Dennis Schirra
 * Author URI:        https://noxsi.io
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       waveCSS
*/

$cssPATH = trailingslashit(dirname(__FILE__));

//first we load alle classes
$dir = new RecursiveDirectoryIterator(plugin_dir_path( __FILE__ ) . 'lib/classes');
foreach (new RecursiveIteratorIterator($dir) as $file) {
    if (!is_dir($file)) {
        if( fnmatch('*.php', $file) ) {
            require $file;
        }
    } }

//check wordpress version
$getversion = new noxsi\helper\Version();
$checkversion = $getversion->versionError($wp_version);




function waveCSSActive() {
	//db creation, create admin options etc.
	global $wpdb;

	$waveCssCollate = ' COLLATE utf8_general_ci';


	$sql0 = "CREATE TABLE `" . $wpdb->prefix . "waveCustomCss` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `css_def` text,
	`page_id` BIGINT unsigned NOT NULL DEFAULT '0',
	  PRIMARY KEY  (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8";



	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	// Use WordpressQuerie to Update
	dbDelta($sql0.$waveCssCollate);


	// initialize the general css def
	$rows_count = $wpdb->get_var( "SELECT COUNT(*) FROM ". $wpdb->prefix ."waveCustomCss;" );
	if (!$rows_count) {
		waveCssInsertSettingsRecord(1);
	}




}

// Drop Table at uninstall the Plugin
function waveCssUninstall() {
	global $wpdb;
	$sql = "DROP TABLE IF EXISTS `" . $wpdb->prefix . "waveCustomCss`";
	$wpdb->query($sql);
}

function waveCssInsertSettingsRecord($player_id) {
	global $wpdb;
	$wpdb->insert(
			$wpdb->prefix . "waveCustomCss",
			array(
				'css_def' => '/*  Add the custom css for your entire website here  */',
				'page_id' => 0
			),
			array(
				'%s',
				'%d'
			)
		);
}



function waveCssLoadStyles() {
	if(strpos($_SERVER['PHP_SELF'], 'wp-admin') !== false) { //loads css in admin
	    //get active Page
        if ( isset( $_GET['page'] ) ) {
            $page = $_GET['page'] ? $_GET['page'] : '';
        }
			if(preg_match('/waveCustomCss/i', $page)) {
				wp_enqueue_style('waveCssAdminFile', plugins_url('css/styles.css', __FILE__));
			}
	} else if (!is_admin()) { //loads css in front-end
			wp_enqueue_style('waveCssGeneralCss', plugins_url('src/assets/waveCssDefinitions.css?d='.time(), __FILE__));
	}
}



function waveCssAddMetaBox() {

    // box on Post and Pages
	$screens = array( 'post', 'page' );

	foreach ( $screens as $screen ) {

		add_meta_box(
			'waveCssSectionId',
			__( 'waveCSS - Add Here Your Custom CSS, Only For This Page:', 'waveCssTextDomain' ),
			'waveCssMetaBoxCallback',
			$screen
		);
	}
}
add_action( 'add_meta_boxes', 'waveCssAddMetaBox' );


// adds the menu pages
function waveCssPluginMenu() {
	add_menu_page('waveCSS Admin Interface', 'noxsi waveCSS', 'edit_posts', 'waveCustomCss', 'waveCssEntrieWebsitePage',
	plugins_url('src/images/noxsiicon.png', __FILE__));
	add_submenu_page( 'waveCustomCss', 'waveCUSTOMCSS Entire Website', 'CSS For Entire Website', 'edit_posts', 'waveCustomCss', 'waveCssEntrieWebsitePage');
	add_submenu_page( 'waveCustomCss', 'waveCUSTOM CSS Help', 'Help', 'edit_posts', 'waveCssHelp', 'waveCssHelpPage');
}


/**
 * Prints the box content.
 *
 * @param WP_Post $post The object for the current post/page.
 */
function waveCssMetaBoxCallback( $post ) {

	// Add an nonce field so we can check for it later.
	wp_nonce_field( 'waveCssMetaBox', 'waveCssMetaBoxNonce' );

	/*
	 * Use get_post_meta() to retrieve an existing value
	 * from the database and use the value for the form.
	 */
	$value = get_post_meta( $post->ID, '_my_meta_value_key_css_def', true );

	echo '<textarea rows="10" name="waveCssOnlyForPage" id="waveCssOnlyForPage" style="width: 100%;">'. esc_attr( $value ) .'</textarea>';
	//echo '<input type="text" id="waveCssOnlyForPage" name="waveCssOnlyForPage" value="' . esc_attr( $value ) . '" size="50" />';
}


/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function waveCssMetaBoxData( $post_id ) {

	/*
	 * We need to verify this came from our screen and with proper authorization,
	 * because the save_post action can be triggered at other times.
	 */

	// Check if our nonce is set.
	if ( ! isset( $_POST['waveCssMetaBoxNonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['waveCssMetaBoxNonce'], 'waveCssMetaBox' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	} else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

	/* OK, it's safe for us to save the data now. */

	// Make sure that it is set.
	if ( ! isset( $_POST['waveCssOnlyForPage'] ) ) {
		return;
	}

	// Sanitize user input. formating.php
	$my_data =  sanitize_textarea_field(sanitize_text_field($_POST['waveCssOnlyForPage']));

	// Update the meta field in the database.
	update_post_meta( $post_id, '_my_meta_value_key_css_def', $my_data );
}
add_action( 'save_post', 'waveCssMetaBoxData' );



function waveCssAddForThisPage() {
	global $post;
	$my_meta = get_post_meta($post->ID,'_my_meta_value_key_css_def',TRUE);
	if ($my_meta) {
		echo '<style type="text/css">'.$my_meta.'</style>';
	}
}
add_action( 'wp_footer','waveCssAddForThisPage' );


//HTML content for entire_website page
function waveCssEntrieWebsitePage()
{
	global $wpdb;
	global $versionError;
	global $cssPATH;

	if(array_key_exists('Submit',sanitize_text_field( $_POST) ) && sanitize_text_field($_POST['Submit']) == 'Update Custom CSS') {
			$wpdb->update(
				$wpdb->prefix .'waveCustomCss',
				array(
				'css_def' => $_POST['css_def']
				),
				array( 'id' => 1 )
			);
			?>
			<div id="message" class="updated"><p><?php echo $versionError['data_saved']?></p></div>
	<?php
	}


	//echo "WP_PLUGIN_URL: ".WP_PLUGIN_URL;
	$safe_sql=$wpdb->prepare( "SELECT * FROM (".$wpdb->prefix ."waveCustomCss) WHERE id = %d",1 );
	$row = $wpdb->get_row($safe_sql,ARRAY_A);
	$row=waveCssUnstripArray($row);
	//write the general css file start
	if(array_key_exists('Submit', sanitize_text_field($_POST)) && sanitize_text_field($_POST['Submit']) == 'Update Custom CSS') {
		$filename=plugin_dir_path(__FILE__) . 'src/assets/waveCssDefinitions.css';
		$fp = fopen($filename, 'w+');
		$fwrite = fwrite($fp, $row['css_def']);
	}
	//write the general css file end
    $_POST = $row;
	$_POST=waveCssUnstripArray(sanitize_text_field($_POST));




	include_once($cssPATH . 'src/templates/entireWebsite.php');
}


function waveCssHelpPage()
{

	global $cssPATH;
	include_once($cssPATH . 'src/templates/help.php');
}

register_activation_hook(__FILE__,"waveCSSActive"); //activate plugin and create the database
register_uninstall_hook(__FILE__, 'waveCssUninstall'); // on unistall delete all databases
add_action('init', 'waveCssLoadStyles');	// loads required styles
add_action('admin_menu', 'waveCssPluginMenu'); // create menus



/** OTHER FUNCTIONS **/

//stripslashes for an entire array
function waveCssUnstripArray($array){
	if (is_array($array)) {
		foreach($array as &$val){
			if(is_array($val)){
				$val = unstrip_array($val);
			} else {
				$val = stripslashes($val);

			}
		}
	}
	return $array;
}

