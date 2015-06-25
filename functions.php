<?php
require_once dirname( __FILE__ ) . '/inc/metabox.php';

add_action( 'after_setup_theme', 'ferinatheme_after_setup' );
function ferinatheme_after_setup(){
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action('wp_head', 'wp_generator');
	add_filter('show_admin_bar', '__return_false');

	add_theme_support('automatic-feed-links');
	add_theme_support('post-thumbnails');
	add_theme_support('custom-header');
	add_theme_support('custom-background');

	add_image_size( 'product-medium', 400, 400, true );

	add_filter('widget_text', 'do_shortcode');

	add_action('init', 'register_ferina_session', 1);
	add_action('init', 'ferina_session');
	add_action('init', 'register_product_init');
	add_action('init', 'ferina_menu');
	add_action('init', 'ferina_widget');

	if ( is_admin() ){
		add_action( 'load-post.php', 'ferina_meta_boxes' );
		add_action( 'load-post-new.php', 'ferina_meta_boxes' );
	}
}

function register_ferina_session(){
	global $wpsession;
	if( ! $_COOKIE['_FERINA_ID'] ){
		session_name('_FERINA_ID');
		session_start();
		$wpsession = array();
		set_transient( 'wp_session_' . $_COOKIE['_FERINA_ID'], serialize($wpsession) );
	}

	$wpsession = unserialize(get_transient( 'wp_session_' . $_COOKIE['_FERINA_ID'] ));
}

function set_wp_session($key, $value){
	global $wpsession;
	$wpsessnew = array();
	delete_transient( 'wp_session_' . $_COOKIE['_FERINA_ID'] );
	if (array_key_exists('first', $search_array)) {
		foreach ( $wpsession as $k => $v ) {
			if ( $key == $k ){
				$wpsessnew[$k] = $value;
			} else {
				$wpsessnew[$k] = $v;
			}
		}
	} else {
		$wpsessnew = $wpsession;
		$wpsessnew[$key] = $value;
	}
	set_transient( 'wp_session_' . $_COOKIE['_FERINA_ID'], serialize($wpsessnew) );
}

function get_wp_session($key){
	global $wpsession;
	return $wpsession[$key];
}

function unset_wp_session($key = ""){
	global $wpsession;
	$wpsessnew = array();
	$wpsessnew = $wpsession;
	delete_transient( 'wp_session_' . $_COOKIE['_FERINA_ID'] );
	if ( $key != "" ){
		unset($wpsessnew[$key]);
		set_transient( 'wp_session_' . $_COOKIE['_FERINA_ID'], serialize($wpsessnew) );
	} else {
		$wpsessnew = array();
	}
	$wpsession = $wpsessnew;
}

function destroy_wp_session(){
	delete_transient( 'wp_session_' . $_COOKIE['_FERINA_ID'] );
	if ( isset( $_COOKIE['_FERINA_ID'] ) ){
		unset( $_COOKIE['_FERINA_ID'] );
	}
}

function open_session(){
	set_wp_session('is_open', true);
	set_wp_session('ferinacart', array());
	set_wp_session('ferinauser', array());
}

function ferina_session(){
	if( get_wp_session('is_open') ){
		open_session();
	}
}

function add_to_cart(){
	$id = $_GET['id'];
	$type = $_GET['type'];
	$jumlah = $_GET['jumlah'];
	$warna = $_GET['warna'];
	$size = $_GET['size'];

	$arrs[] = array(
		'id' => $id,
		'type' => $type,
		'jumlah' => $jumlah,
		'warna' => $warna,
		'size' => $size,
	);
	set_wp_session('ferinacart', serialize($arrs));
}

function countarrayvalue($arr, $key){
	$ret = 0;
	foreach ($arr as $k => $v) {
		$ret += (int)$v[$key];
	}
	return $ret;
}

function currency($string) {}
function ferinaprivacypages(){}
function get_profile_url(){}
function catch_that_image(){}

function warna_numeric_posts_nav(){
	global $wp_query;

	if( is_singular() )
		return;
	
	if( $wp_query->max_num_pages <= 1 )
		return;

	$paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
	$max   = intval( $wp_query->max_num_pages );
	/**	Add current page to the array */
	if ( $paged >= 1 )
		$links[] = $paged;
	/**	Add the pages around the current page to the array */
	if ( $paged >= 3 ) {
		$links[] = $paged - 1;
		$links[] = $paged - 2;
	}
	if ( ( $paged + 2 ) <= $max ) {
		$links[] = $paged + 2;
		$links[] = $paged + 1;
	}
	echo '<div class="navigation"><ul>' . "\n";
	/**	Previous Post Link */
	if ( get_previous_posts_link() )
		printf( '<li>%s</li>' . "\n", get_previous_posts_link() );
	if ( ! in_array( 1, $links ) ) {
		$class = 1 == $paged ? ' class="active"' : '';

		printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( 1 ) ), '1' );

		if ( ! in_array( 2, $links ) )
			echo '<li>…</li>';
	}
	sort( $links );
	foreach ( (array) $links as $link ) {
		$class = $paged == $link ? ' class="active"' : '';
		printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $link ) ), $link );
	}
	if ( ! in_array( $max, $links ) ) {
		if ( ! in_array( $max - 1, $links ) )
			echo '<li>…</li>' . "\n";

		$class = $paged == $max ? ' class="active"' : '';
		printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $max ) ), $max );
	}
	if ( get_next_posts_link() )
		printf( '<li>%s</li>' . "\n", get_next_posts_link() );
	echo '</ul></div>' . "\n";
}

function ferina_menu(){
	register_nav_menus( 
		array(
		   'primary' => 'Primary Menu',
		   'bottom'  => 'footer Menu' 
	    )
	);
}

function ferina_widget(){
	register_sidebar(
		array(
			'name'=> __('Primary Sidebar', 'h5'),
			'id' => 'widgets_sidebar',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<h3 class="widgettitle">',
			'after_title' => '</h3>'
		)
	);

	register_sidebar(
		array(
			'name'=> __('Footer Sidebar 1', 'h5'),
			'id' => 'f_sidebar1',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<h3 class="widgettitle">',
			'after_title' => '</h3>'
		)
	);

	register_sidebar(
		array(
			'name'=> __('Footer Sidebar 2', 'h5'),
			'id' => 'f_sidebar2',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<h3 class="widgettitle">',
			'after_title' => '</h3>'
		)
	);

	register_sidebar(
		array(
			'name'=> __('Footer Sidebar 3', 'h5'),
			'id' => 'f_sidebar3',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<h3 class="widgettitle">',
			'after_title' => '</h3>'
		)
	);
}

function socialloginhtml(){
	$html  = '<p class="login-social">'. __('Or Login With', 'ferina') .'</p>';
	$html .= '<ul class="icon-login-sos">';
		$html .= '<li><a class="social-login facebook" href="#" title="'. __('Facebook', 'ferina') .'"><img src="'. get_bloginfo('template_url') .'/lib/img/fb.png" alt="'. __('Login With Facebook', 'ferina') .'"></a></li>';
		$html .= '<li><a class="social-login twitter" href="#" title="'. __('Twitter', 'ferina') .'"><img src="'. get_bloginfo('template_url') .'/lib/img/tw.png" alt="'. __('Login With Twitter', 'ferina') .'"></a></li>';
		$html .= '<li><a class="social-login path" href="#" title="'. __('Path', 'ferina') .'"><img src="'. get_bloginfo('template_url') .'/lib/img/path.png" alt="'. __('Login With Path', 'ferina') .'"></a></li>';
		$html .= '<li><a class="social-login instagram" href="#" title="'. __('Instagram', 'ferina') .'"><img src="'. get_bloginfo('template_url') .'/lib/img/ins.png" alt="'. __('Login With Instagram', 'ferina') .'"></a></li>';
		$html .= '<li><a class="social-login google" href="#" title="'. __('Google Plus', 'ferina') .'"><img src="'. get_bloginfo('template_url') .'/lib/img/g+.png" alt="'. __('Login With Google Plus', 'ferina') .'"></a></li>';
	$html .= '</ul>';
	return $html;
}

function ferinagreeting(){
	if ( get_wp_session('ferinauser') ){
		$namagw = __('Hi Member', 'ferina');
		$firsturl = '<a href="' . get_profile_url() . '">'. __('My Account', 'ferina').'</a>';
		$secondurl = '<a href="#" title="'. __('Logout', 'ferina') . '" id="menu-account-logout">'. __('Logout', 'ferina') . '</a>';
	} else {
		$namagw = __('My Account', 'ferina');
		$firsturl = '<a title="' . __('Login', 'ferina') . '" id="menu-account-login" href="#">' . __('Login', 'ferina') . '</a>';
		$secondurl = '<a title="' . __('Register', 'ferina') . '" id="menu-account-register" class="Open_reg" href="#">' . __('Register', 'ferina') . '</a>';
	}
	return array('greeting' => $namagw, 'urlsatu' => $firsturl, 'urldua' => $secondurl);
}

class CSS_Menu_Maker_Walker extends Walker {
	var $db_fields = array( 'parent' => 'menu_item_parent', 'id' => 'db_id' );
	
	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "\n$indent<ul class='bukatutup'>\n";
	}

	function end_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}

	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		global $wp_query;
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
		$class_names = $value = ''; 
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		/* Add active class */
		if(in_array('current-menu-item', $classes)) {
			$classes[] = 'active';
			unset($classes['current-menu-item']);
		}
		/* Check for children */
		$children = get_posts(array('post_type' => 'nav_menu_item', 'nopaging' => true, 'numberposts' => 1, 'meta_key' => '_menu_item_menu_item_parent', 'meta_value' => $item->ID));
		if (!empty($children)) {
			$classes[] = 'has-sub';
		}
		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';
		$id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
		$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';
		$output .= $indent . '<li' . $id . $value . $class_names .'>';
		$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
		$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
		$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
		$attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
		$item_output = $args->before;
		$item_output .= '<a'. $attributes .'><span>';
		$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
		$item_output .= '</span></a>';
		$item_output .= $args->after;
		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

	function end_el( &$output, $item, $depth = 0, $args = array() ) {
		$output .= "</li>\n";
	}
}

function register_product_init() {
	$label = array(
		'name'               => _x( 'Wholesale Product', 'post type general name', 'ferina' ),
		'singular_name'      => _x( 'Wholesale Product', 'post type singular name', 'ferina' ),
		'menu_name'          => _x( 'Wholesale Products', 'admin menu', 'ferina' ),
		'name_admin_bar'     => _x( 'Wholesale Product', 'add new on admin bar', 'ferina' ),
		'add_new'            => _x( 'Add New', 'wholesale-product', 'ferina' ),
		'add_new_item'       => __( 'Add New Wholesale Product', 'ferina' ),
		'new_item'           => __( 'New Product', 'ferina' ),
		'edit_item'          => __( 'Edit Product', 'ferina' ),
		'view_item'          => __( 'View Product', 'ferina' ),
		'all_items'          => __( 'All Products', 'ferina' ),
		'search_items'       => __( 'Search Products', 'ferina' ),
		'parent_item_colon'  => __( 'Parent Products:', 'ferina' ),
		'not_found'          => __( 'No products found.', 'ferina' ),
		'not_found_in_trash' => __( 'No products found in Trash.', 'ferina' )
	);

	$arg = array(
		'labels'             => $label,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'wholesale-product' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title' )
	);

	$labels = array(
		'name'               => _x( 'Retail Product', 'post type general name', 'ferina' ),
		'singular_name'      => _x( 'Retail Product', 'post type singular name', 'ferina' ),
		'menu_name'          => _x( 'Retail Products', 'admin menu', 'ferina' ),
		'name_admin_bar'     => _x( 'Retail Product', 'add new on admin bar', 'ferina' ),
		'add_new'            => _x( 'Add New', 'retail-product', 'ferina' ),
		'add_new_item'       => __( 'Add New Retail Product', 'ferina' ),
		'new_item'           => __( 'New Product', 'ferina' ),
		'edit_item'          => __( 'Edit Product', 'ferina' ),
		'view_item'          => __( 'View Product', 'ferina' ),
		'all_items'          => __( 'All Products', 'ferina' ),
		'search_items'       => __( 'Search Products', 'ferina' ),
		'parent_item_colon'  => __( 'Parent Products:', 'ferina' ),
		'not_found'          => __( 'No products found.', 'ferina' ),
		'not_found_in_trash' => __( 'No products found in Trash.', 'ferina' )
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'retail-product' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title' )
	);

	register_post_type( 'wholesale-product', $arg );
	register_post_type( 'retail-product', $args );

	$stylelabels = array(
		'name'              => _x( 'Styles', 'taxonomy general name', 'ferina'  ),
		'singular_name'     => _x( 'Style', 'taxonomy singular name', 'ferina'  ),
		'search_items'      => __( 'Search Styles', 'ferina' ),
		'all_items'         => __( 'All Styles', 'ferina' ),
		'parent_item'       => __( 'Parent Style', 'ferina' ),
		'parent_item_colon' => __( 'Parent Style:', 'ferina' ),
		'edit_item'         => __( 'Edit Style', 'ferina' ),
		'update_item'       => __( 'Update Style', 'ferina' ),
		'add_new_item'      => __( 'Add New Style', 'ferina' ),
		'new_item_name'     => __( 'New Style Name', 'ferina' ),
		'menu_name'         => __( 'Style', 'ferina' ),
	);

	$styleargs = array(
		'hierarchical'      => true,
		'labels'            => $stylelabels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'style' ),
	);

	register_taxonomy( 'style', array( 'retail-product', 'wholesale-product' ), $styleargs );

	$sizelabels = array(
		'name'              => _x( 'Sizes', 'taxonomy general name', 'ferina' ),
		'singular_name'     => _x( 'Size', 'taxonomy singular name', 'ferina' ),
		'search_items'      => __( 'Search Sizes', 'ferina' ),
		'all_items'         => __( 'All Sizes', 'ferina' ),
		'parent_item'       => __( 'Parent Size', 'ferina' ),
		'parent_item_colon' => __( 'Parent Size:', 'ferina' ),
		'edit_item'         => __( 'Edit Size', 'ferina' ),
		'update_item'       => __( 'Update Size', 'ferina' ),
		'add_new_item'      => __( 'Add New Size', 'ferina' ),
		'new_item_name'     => __( 'New Size Name', 'ferina' ),
		'menu_name'         => __( 'Size', 'ferina' ),
	);

	$sizeargs = array(
		'hierarchical'      => false,
		'labels'            => $sizelabels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'size' ),
	);

	$colorlabels = array(
		'name'              => _x( 'Colours', 'taxonomy general name', 'ferina' ),
		'singular_name'     => _x( 'Colour', 'taxonomy singular name', 'ferina' ),
		'search_items'      => __( 'Search Colours', 'ferina' ),
		'all_items'         => __( 'All Colours', 'ferina' ),
		'parent_item'       => __( 'Parent Colour', 'ferina' ),
		'parent_item_colon' => __( 'Parent Colour:', 'ferina' ),
		'edit_item'         => __( 'Edit Colour', 'ferina' ),
		'update_item'       => __( 'Update Colour', 'ferina' ),
		'add_new_item'      => __( 'Add New Colour', 'ferina' ),
		'new_item_name'     => __( 'New Style Colour', 'ferina' ),
		'menu_name'         => __( 'Colour', 'ferina' ),
	);

	$colorargs = array(
		'hierarchical'      => false,
		'labels'            => $colorlabels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'colour' ),
	);

	register_taxonomy( 'colour', array( 'retail-product' ), $colorargs );
	register_taxonomy( 'size', array( 'retail-product' ), $sizeargs );
}
