<?php
/**
 * هدر قالب بازار
 *
 * @package Bazaar
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#bz-content">پرش به محتوا</a>

<!-- نوار بالا -->
<div class="bz-topbar">
	<div class="container">
		<span class="bz-topbar-note"><?php echo esc_html( bz_get_option( 'topbar_text' ) ); ?></span>
		<div class="bz-topbar-links">
			<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">وبلاگ</a>
			<a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">درباره ما</a>
			<span class="bz-topbar-phone"><?php echo bz_icon( 'phone', 14 ); ?> <?php echo esc_html( bz_get_option( 'footer_phone' ) ); ?></span>
		</div>
	</div>
</div>

<!-- هدر اصلی -->
<header class="bz-header" id="bz-header">
	<div class="container bz-header-inner">

		<button class="bz-burger" id="bz-burger" aria-label="باز کردن منو" aria-expanded="false">
			<?php echo bz_icon( 'menu', 26 ); ?>
		</button>

		<div class="bz-brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<span class="bz-brand-logo"><?php echo bz_icon( 'cart', 24 ); ?></span>
			<?php endif; ?>
			<span class="bz-brand-text">
				<b><?php bloginfo( 'name' ); ?></b>
				<span><?php bloginfo( 'description' ); ?></span>
			</span>
		</div>

		<div class="bz-search">
			<?php get_search_form(); ?>
		</div>

		<div class="bz-header-actions">
			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<a class="bz-action" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" title="حساب کاربری">
					<?php echo bz_icon( 'user', 22 ); ?>
					حساب من
				</a>
				<?php bz_cart_link(); ?>
			<?php endif; ?>
		</div>
	</div>

	<!-- منوی اصلی -->
	<nav class="bz-nav" aria-label="منوی اصلی">
		<div class="container">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'bz-menu',
					'fallback_cb'    => false,
				) );
			} else {
				echo '<ul class="bz-menu">';
				if ( class_exists( 'WooCommerce' ) ) {
					echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">خانه</a></li>';
					echo '<li><a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">فروشگاه</a></li>';
				}
				echo '</ul>';
			}
			?>
			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<span class="bz-nav-cta"><?php echo bz_icon( 'truck', 16 ); ?> ارسال به سراسر ایران</span>
			<?php endif; ?>
		</div>
	</nav>
</header>

<!-- منوی موبایل -->
<div class="bz-drawer" id="bz-drawer">
	<div class="bz-drawer-panel">
		<button class="bz-drawer-close" id="bz-drawer-close" aria-label="بستن منو">✕</button>
		<div class="bz-search" style="display:block;margin-bottom:18px"><?php get_search_form(); ?></div>
		<?php
		if ( has_nav_menu( 'primary' ) ) {
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'bz-menu',
				'fallback_cb'    => false,
			) );
		}
		?>
	</div>
</div>

<main id="bz-content">
