<?php
/**
 * تنظیمات سفارشی‌ساز (Customizer) قالب بازار
 *
 * @package Bazaar
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function bz_customize_register( $wp_customize ) {

	/* ===== بخش تنظیمات بازار ===== */
	$wp_customize->add_panel( 'bz_panel', array(
		'title'    => __( 'تنظیمات قالب بازار', 'bazaar' ),
		'priority' => 10,
	) );

	/* --- رنگ‌ها --- */
	$wp_customize->add_section( 'bz_colors', array(
		'title' => __( 'رنگ‌ها', 'bazaar' ),
		'panel' => 'bz_panel',
	) );

	$wp_customize->add_setting( 'bz_color_primary', array(
		'default'           => '#0f766e',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'bz_color_primary', array(
		'label'   => __( 'رنگ اصلی برند', 'bazaar' ),
		'section' => 'bz_colors',
	) ) );

	$wp_customize->add_setting( 'bz_color_accent', array(
		'default'           => '#f59e0b',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	) );
	$wp_customize->add_Control( new WP_Customize_Color_Control( $wp_customize, 'bz_color_accent', array(
		'label'   => __( 'رنگ تاکیدی (دکمه‌ها و قیمت‌ها)', 'bazaar' ),
		'section' => 'bz_colors',
	) ) );

	/* --- نوار بالای سایت --- */
	$wp_customize->add_section( 'bz_topbar', array(
		'title' => __( 'نوار بالای سایت', 'bazaar' ),
		'panel' => 'bz_panel',
	) );

	$wp_customize->add_setting( 'bz_topbar_text', array(
		'default'           => bz_defaults()['topbar_text'],
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'bz_topbar_text', array(
		'label'   => __( 'متن نوار بالا', 'bazaar' ),
		'section' => 'bz_topbar',
		'type'    => 'text',
	) );

	/* --- بخش قهرمان (Hero) صفحه نخست --- */
	$wp_customize->add_section( 'bz_hero', array(
		'title' => __( 'بخش اصلی صفحه نخست (Hero)', 'bazaar' ),
		'panel' => 'bz_panel',
	) );

	$hero_fields = array(
		'hero_title'    => array( __( 'عنوان اصلی', 'bazaar' ), 'textarea' ),
		'hero_subtitle' => array( __( 'توضیحات', 'bazaar' ), 'textarea' ),
		'hero_btn'      => array( __( 'متن دکمه اصلی', 'bazaar' ), 'text' ),
		'hero_btn_url'  => array( __( 'لینک دکمه اصلی (خالی = صفحه فروشگاه)', 'bazaar' ), 'url' ),
	);
	foreach ( $hero_fields as $key => $field ) {
		$wp_customize->add_setting( 'bz_' . $key, array(
			'default'           => bz_defaults()[ $key ],
			'sanitize_callback' => ( 'textarea' === $field[1] ) ? 'wp_kses_post' : 'sanitize_text_field',
		) );
		$wp_customize->add_control( 'bz_' . $key, array(
			'label'   => $field[0],
			'section' => 'bz_hero',
			'type'    => $field[1],
		) );
	}

	/* --- فوتر --- */
	$wp_customize->add_section( 'bz_footer', array(
		'title' => __( 'فوتر', 'bazaar' ),
		'panel' => 'bz_panel',
	) );

	$footer_fields = array(
		'footer_about' => array( __( 'متن درباره فروشگاه', 'bazaar' ), 'textarea' ),
		'footer_phone' => array( __( 'تلفن پشتیبانی', 'bazaar' ), 'text' ),
		'footer_email' => array( __( 'ایمیل', 'bazaar' ), 'text' ),
		'footer_addr'  => array( __( 'آدرس', 'bazaar' ), 'text' ),
	);
	foreach ( $footer_fields as $key => $field ) {
		$wp_customize->add_setting( 'bz_' . $key, array(
			'default'           => bz_defaults()[ $key ],
			'sanitize_callback' => ( 'textarea' === $field[1] ) ? 'wp_kses_post' : 'sanitize_text_field',
		) );
		$wp_customize->add_control( 'bz_' . $key, array(
			'label'   => $field[0],
			'section' => 'bz_footer',
			'type'    => $field[1],
		) );
	}

	/* --- سایر گزینه‌ها --- */
	$wp_customize->add_section( 'bz_misc', array(
		'title' => __( 'گزینه‌های دیگر', 'bazaar' ),
		'panel' => 'bz_panel',
	) );

	$wp_customize->add_setting( 'bz_fa_digits', array(
		'default'           => 1,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'bz_fa_digits', array(
		'label'   => __( 'نمایش اعداد به صورت فارسی (۰۱۲۳...)', 'bazaar' ),
		'section' => 'bz_misc',
		'type'    => 'checkbox',
	) );
}
add_action( 'customize_register', 'bz_customize_register' );
