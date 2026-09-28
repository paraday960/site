<?php
/**
 * قالب نتایج جست‌وجو
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
	<?php if ( have_posts() ) : ?>
		<div class="bz-posts-grid">
			<?php while ( have_posts() ) : the_post(); ?>
				<article <?php post_class( 'bz-post-card' ); ?>>
					<a class="bz-post-thumb" href="<?php the_permalink(); ?>">
						<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'medium_large' ); } ?>
					</a>
					<div class="bz-post-body">
						<div class="bz-post-meta">
							<time>📅 <?php echo esc_html( bz_post_date() ); ?></time>
						</div>
						<h2><a href="<?php the_permalink(); ?>"><?php echo esc_html( get_the_title() ); ?></a></h2>
						<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24, '…' ) ); ?></p>
						<a class="bz-post-more" href="<?php the_permalink(); ?>">مشاهده <span>‹</span></a>
					</div>
				</article>
			<?php endwhile; ?>
		</div>
		<div class="bz-pagination">
			<?php the_posts_pagination( array( 'prev_text' => '‹', 'next_text' => '›' ) ); ?>
		</div>
	<?php else : ?>
		<div class="bz-no-results">
			<h3>نتیجه‌ای پیدا نشد</h3>
			<p>عبارت دیگری را امتحان کنید یا از فروشگاه خرید کنید.</p>
			<div style="max-width:420px;margin:18px auto 0"><?php get_search_form(); ?></div>
		</div>
	<?php endif; ?>
</div>
<?php get_footer(); ?>
