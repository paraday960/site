<?php
/**
 * Bazaar | بازار — قالب فروشگاهی فارسی
 *
 * @package Bazaar
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'BZ_VERSION', '1.0.0' );
define( 'BZ_DIR', get_template_directory() );
define( 'BZ_URI', get_template_directory_uri() );

/* -------------------------------------------------------
 * راه‌اندازی قالب
 * ----------------------------------------------------- */
function bz_setup() {
	load_theme_textdomain( 'bazaar', BZ_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'customize-selective-refresh-widgets' );

	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );

	add_theme_support( 'custom-logo', array(
		'height'      => 64,
		'width'       => 220,
		'flex-height' => true,
		'flex-width'  => true,
	) );

	/* پشتیبانی کامل از ووکامرس */
	add_theme_support( 'woocommerce', array(
		'product_grid'        => array( 'default_columns' => 4, 'min_columns' => 2, 'max_columns' => 5 ),
		'single_image_width'  => 800,
		'thumbnail_image_width' => 480,
	) );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus( array(
		'primary' => __( 'منوی اصلی', 'bazaar' ),
		'footer'  => __( 'منوی فوتر', 'bazaar' ),
	) );
}
add_action( 'after_setup_theme', 'bz_setup' );

function bz_content_width() {
	$GLOBALS['content_width'] = 1280;
}
add_action( 'after_setup_theme', 'bz_content_width', 0 );

/* -------------------------------------------------------
 * بارگذاری استایل و اسکریپت
 * ----------------------------------------------------- */
function bz_assets() {
	wp_enqueue_style( 'bazaar-style', get_stylesheet_uri(), array(), BZ_VERSION );
	wp_enqueue_script( 'bazaar-main', BZ_URI . '/assets/js/main.js', array(), BZ_VERSION, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'bz_assets' );

/* -------------------------------------------------------
 * فارسی و راست‌چین بودن سایت (حتی اگر زبان هسته انگلیسی باشد)
 * ----------------------------------------------------- */
function bz_language_attributes( $output ) {
	return 'dir="rtl" lang="fa"';
}
add_filter( 'language_attributes', 'bz_language_attributes' );

/* -------------------------------------------------------
 * تبدیل ارقام انگلیسی به فارسی
 * ----------------------------------------------------- */
function bz_fa_digits( $str ) {
	if ( ! bz_get_option( 'fa_digits', 1 ) ) { return $str; }
	$en = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
	$fa = array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' );
	return str_replace( $en, $fa, (string) $str );
}

/* تبدیل ارقام قیمت‌های ووکامرس به فارسی */
function bz_fa_price( $return ) {
	return bz_fa_digits( $return );
}
add_filter( 'wc_price', 'bz_fa_price' );

/* -------------------------------------------------------
 * گزینه‌های قالب (با مقادیر پیش‌فرض)
 * ----------------------------------------------------- */
function bz_defaults() {
	return array(
		'fa_digits'    => 1,
		'topbar_text'  => 'ارسال رایگان برای سفارش‌های بالای ۵۰۰ هزار تومان',
		'hero_title'   => 'هر چی که لازم داری، <em>با چند کلیک</em> در خانه‌ات',
		'hero_subtitle'=> 'بازار، فروشگاه اینترنتی شما برای خرید کالای اصل و اورجینال؛ از موبایل و لپ‌تاپ تا پوشاک و محصولات دانلودی — با ارسال سریع و ضمانت بازگشت کالا.',
		'hero_btn'     => 'شروع خرید',
		'hero_btn_url' => '',
		'footer_about' => 'بازار یک فروشگاه اینترنتی کامل است که با وردپرس و ووکامرس ساخته شده و از تمام انواع محصولات (ساده، متغیر، دانلودی، خارجی و گروهی) پشتیبانی می‌کند.',
		'footer_phone' => '۰۲۱-۹۱۰۰۰۰۰۰',
		'footer_email' => 'info@bazaar-shop.ir',
		'footer_addr'  => 'تهران، خیابان ولیعصر، برج بازار، طبقه ۷',
	);
}

function bz_get_option( $key, $default = null ) {
	$defaults = bz_defaults();
	$default  = ( null === $default && isset( $defaults[ $key ] ) ) ? $defaults[ $key ] : $default;
	return get_theme_mod( 'bz_' . $key, $default );
}

/* -------------------------------------------------------
 * متغیرهای رنگ سفارشی (از سفارشی‌ساز)
 * ----------------------------------------------------- */
function bz_css_vars() {
	$primary = get_theme_mod( 'bz_color_primary', '#0f766e' );
	$accent  = get_theme_mod( 'bz_color_accent', '#f59e0b' );
	?>
	<style id="bazaar-vars">
		:root {
			--bz-primary: <?php echo esc_attr( $primary ); ?>;
			--bz-primary-dark: <?php echo esc_attr( bz_darken( $primary, .12 ) ); ?>;
			--bz-accent: <?php echo esc_attr( $accent ); ?>;
			--bz-accent-dark: <?php echo esc_attr( bz_darken( $accent, .12 ) ); ?>;
		}
	</style>
	<?php
}
add_action( 'wp_head', 'bz_css_vars' );

/* تابع کمکی برای تیره کردن رنگ hex */
function bz_darken( $hex, $amount = .1 ) {
	$hex = ltrim( $hex, '#' );
	if ( 3 === strlen( $hex ) ) { $hex = preg_replace( '/(.)/', '$1$1', $hex ); }
	$r = hexdec( substr( $hex, 0, 2 ) );
	$g = hexdec( substr( $hex, 2, 2 ) );
	$b = hexdec( substr( $hex, 4, 2 ) );
	$r = max( 0, min( 255, round( $r * ( 1 - $amount ) ) ) );
	$g = max( 0, min( 255, round( $g * ( 1 - $amount ) ) ) );
	$b = max( 0, min( 255, round( $b * ( 1 - $amount ) ) ) );
	return sprintf( '#%02x%02x%02x', $r, $g, $b );
}

/* -------------------------------------------------------
 * فایل‌های کمکی
 * ----------------------------------------------------- */
require BZ_DIR . '/inc/customizer.php';
require BZ_DIR . '/inc/template-tags.php';
if ( class_exists( 'WooCommerce' ) ) {
	require BZ_DIR . '/inc/woocommerce.php';
}

/* -------------------------------------------------------
 * ویجت‌ها
 * ----------------------------------------------------- */
function bz_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'سایدبار وبلاگ', 'bazaar' ),
		'id'            => 'sidebar-1',
		'before_widget' => '<section id="%1$s" class="bz-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="bz-widget-title">',
		'after_title'   => '</h3>',
	) );
}
add_action( 'widgets_init', 'bz_widgets_init' );

/* حذف نوار مدیریت وردپرس در نسخه نمایشی فروشگاه */
add_filter( 'show_admin_bar', '__return_false' );
