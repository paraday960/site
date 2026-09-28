<?php
/**
 * قالب اصلی (فهرست نوشته‌ها و آرشیوها)
 *
 * @package Bazaar
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();
?>
<div class="bz-page-title-wrap">
	<div class="container">
		<h1 class="bz-page-title"><?php echo esc_html( bz_archive_title() ); ?></h1>
	</div>
</div>

<div class="container bz-archive">
	<div class="bz-posts-grid">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<article <?php post_class( 'bz-post-card' ); ?>>
					<a class="bz-post-thumb" href="<?php the_permalink(); ?>">
						<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'medium_large' ); } ?>
					</a>
					<div class="bz-post-body">
						<div class="bz-post-meta">
							<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">📅 <?php echo esc_html( bz_post_date() ); ?></time>
							<?php $bz_cat = get_the_category(); if ( $bz_cat ) : ?>
								<span class="bz-post-cat">🏷 <?php echo esc_html( $bz_cat[0]->name ); ?></span>
							<?php endif; ?>
						</div>
						<h2><a href="<?php the_permalink(); ?>"><?php echo esc_html( get_the_title() ); ?></a></h2>
						<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24, '…' ) ); ?></p>
						<a class="bz-post-more" href="<?php the_permalink(); ?>">ادامه مطلب <span>‹</span></a>
					</div>
				</article>
			<?php endwhile; ?>
		<?php else : ?>
			<div class="bz-no-results">
				<h3>مطلبی پیدا نشد</h3>
				<p>متاسفانه نوشته‌ای برای نمایش اینجا وجود ندارد.</p>
			</div>
		<?php endif; ?>
	</div>

	<div class="bz-pagination">
		<?php the_posts_pagination( array( 'prev_text' => '‹', 'next_text' => '›' ) ); ?>
	</div>
</div>
<?php get_footer(); ?>
