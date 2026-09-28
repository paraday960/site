<?php
/**
 * یکپارچه‌سازی ووکامرس با قالب بازار
 *
 * پشتیبانی از تمام انواع محصولات: ساده، متغیر، دانلودی، خارجی، گروهی
 *
 * @package Bazaar
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* -------------------------------------------------------
 * افزودن واحد پول «تومان» به ووکامرس
 * ----------------------------------------------------- */
function bz_add_toman_currency( $currencies ) {
	$currencies['IRT'] = 'تومان';
	return $currencies;
}
add_filter( 'woocommerce_currencies', 'bz_add_toman_currency' );

function bz_toman_symbol( $symbol, $currency ) {
	if ( 'IRT' === $currency ) { return 'تومان'; }
	return $symbol;
}
add_filter( 'woocommerce_currency_symbol', 'bz_toman_symbol', 10, 2 );

/* -------------------------------------------------------
 * تعداد ستون‌ها و محصولات در هر صفحه فروشگاه
 * ----------------------------------------------------- */
add_filter( 'loop_shop_columns', fn() => 4 );
add_filter( 'loop_shop_per_page', fn() => 12 );
add_filter( 'woocommerce_pagination_args', function ( $args ) {
	$args['prev_text'] = '‹';
	$args['next_text'] = '›';
	return $args;
} );

/* -------------------------------------------------------
 * افزودن کلاس نوع محصول به کارت‌ها (برای استایل ویژه)
 * ----------------------------------------------------- */
function bz_product_type_class( $classes, $class, $product_id ) {
	$product = wc_get_product( $product_id );
	if ( $product ) {
		$type = $product->get_type();
		$classes[] = 'bz-product-' . $type;
		if ( $product->is_downloadable() ) { $classes[] = 'bz-product-digital'; }
		if ( $product->is_on_sale() ) { $classes[] = 'bz-product-onsale'; }
		if ( $product->is_featured() ) { $classes[] = 'bz-product-featured'; }
	}
	return $classes;
}
add_filter( 'post_class', 'bz_product_type_class', 10, 3 );

/* -------------------------------------------------------
 * برچسب «جدید» برای محصولات تازه
 * ----------------------------------------------------- */
function bz_new_badge() {
	global $product;
	if ( ! $product ) { return; }
	$newness = 30; // روز
	$created = strtotime( $product->get_date_created() );
	if ( $created && ( time() - $created ) < DAY_IN_SECONDS * $newness ) {
		echo '<span class="bz-ribbon-new">جدید</span>';
	}
}
add_action( 'woocommerce_before_shop_loop_item_title', 'bz_new_badge', 9 );

/* -------------------------------------------------------
 * متن دکمه افزودن به سبد بر اساس نوع محصول
 * ----------------------------------------------------- */
function bz_add_to_cart_text( $text, $product ) {
	if ( $product && $product->is_type( 'external' ) ) {
		return $product->get_button_text() ? $product->get_button_text() : 'خرید از فروشنده';
	}
	if ( $product && $product->is_type( 'grouped' ) ) {
		return 'مشاهده محصولات';
	}
	if ( $product && $product->is_type( 'variable' ) ) {
		return 'انتخاب گزینه‌ها';
	}
	if ( $product && $product->is_downloadable() ) {
		return 'افزودن به سبد (دانلودی)';
	}
	return $text;
}
add_filter( 'woocommerce_product_add_to_cart_text', 'bz_add_to_cart_text', 10, 2 );
add_filter( 'woocommerce_product_single_add_to_cart_text', function () { return 'افزودن به سبد خرید'; } );

/* -------------------------------------------------------
 * حذف مقدمات پیش‌فرض و اضافه کردن ساختار دلخواه در حلقه
 * ----------------------------------------------------- */
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 11 );

/* -------------------------------------------------------
 * نان‌ریزه (Breadcrumb) ووکامرس
 * ----------------------------------------------------- */
add_filter( 'woocommerce_breadcrumb_defaults', function ( $defaults ) {
	$defaults['delimiter']   = '<span class="bz-crumb-sep">›</span>';
	$defaults['wrap_before'] = '<nav class="woocommerce-breadcrumb" aria-label="مسیر">';
	$defaults['wrap_after']  = '</nav>';
	$defaults['home']        = 'خانه';
	return $defaults;
} );

/* -------------------------------------------------------
 * اسکلتون صفحه فروشگاه: عنوان و مسیر
 * ----------------------------------------------------- */
function bz_shop_header( $title = '' ) {
	if ( ! $title ) {
		if ( is_shop() ) { $title = 'فروشگاه'; }
		elseif ( is_product_category() ) { $title = single_term_title( '', false ); }
		elseif ( is_product_tag() ) { $title = 'برچسب: ' . single_term_title( '', false ); }
		elseif ( is_search() ) { $title = 'نتایج جست‌وجو'; }
	}
	?>
	<div class="bz-page-title-wrap">
		<div class="container">
			<h1 class="bz-page-title"><?php echo esc_html( $title ); ?></h1>
			<?php if ( function_exists( 'woocommerce_breadcrumb' ) ) { woocommerce_breadcrumb(); } ?>
		</div>
	</div>
	<?php
}

/* -------------------------------------------------------
 * آیکون سبد خرید در هدر (تعداد آیتم‌ها با آجاکس به‌روز می‌شود)
 * ----------------------------------------------------- */
function bz_cart_link() {
	if ( ! function_exists( 'wc_get_cart_url' ) ) { return; }
	$count = function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	?>
	<a class="bz-action" href="<?php echo esc_url( wc_get_cart_url() ); ?>" title="سبد خرید">
		<?php echo bz_icon( 'cart', 22 ); ?>
		<span class="bz-cart-count"><?php echo esc_html( bz_fa_digits( $count ) ); ?></span>
		سبد خرید
	</a>
	<?php
}

add_filter( 'woocommerce_add_to_cart_fragments', function ( $fragments ) {
	$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	$fragments['span.bz-cart-count'] = '<span class="bz-cart-count">' . bz_fa_digits( $count ) . '</span>';
	return $fragments;
} );

/* -------------------------------------------------------
 * افزوده شدن دکمه «ادامه خرید» در سبد خرید
 * ----------------------------------------------------- */
add_action( 'woocommerce_after_cart', function () {
	$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
	echo '<a class="button" style="margin-top:14px" href="' . esc_url( $shop ) . '">ادامه خرید</a>';
} );
