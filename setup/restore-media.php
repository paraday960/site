<?php
/**
 * بازیابی رسانه‌های فروشگاه (تصاویر محصولات + فایل‌های دانلودی + تصاویر وبلاگ)
 * اجرا:  wp eval-file setup/restore-media.php
 *
 * زمانی مفید است که پوشه wp-content/uploads حذف شده باشد ولی دیتابیس سالم باشد.
 * برای هر محصول، اگر فایل تصویرش موجود نباشد، دوباره می‌سازد.
 *
 * @package Bazaar
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
require_once ABSPATH . 'wp-admin/includes/image.php';

echo "=== بازیابی رسانه‌های بازار ===\n";

/* ---------- تصویر جایگزین (همان تولیدکننده دمو) ---------- */
function bz_placeholder( $name, $c1, $c2, $style = 0 ) {
	$W = 700; $H = 700;
	$im = imagecreatetruecolor( $W, $H );
	for ( $y = 0; $y < $H; $y++ ) {
		$t = $y / $H;
		imageline( $im, 0, $y, $W, $y, imagecolorallocate( $im,
			(int) ( $c1[0] + ( $c2[0] - $c1[0] ) * $t ),
			(int) ( $c1[1] + ( $c2[1] - $c1[1] ) * $t ),
			(int) ( $c1[2] + ( $c2[2] - $c1[2] ) * $t ) ) );
	}
	$white = imagecolorallocatealpha( $im, 255, 255, 255, 78 );
	$soft  = imagecolorallocatealpha( $im, 255, 255, 255, 108 );
	switch ( $style % 6 ) {
		case 0: imagesetthickness( $im, 26 ); imageellipse( $im, 350, 330, 300, 300, $white ); imageellipse( $im, 350, 330, 180, 180, $soft ); imagefilledellipse( $im, 350, 330, 66, 66, $white ); break;
		case 1: imagesetthickness( $im, 34 ); for ( $i = -2; $i < 7; $i++ ) { imageline( $im, $i * 140, 700, $i * 140 + 400, 0, $white ); } imagesetthickness( $im, 10 ); for ( $i = -2; $i < 7; $i++ ) { imageline( $im, $i * 140 + 70, 700, $i * 140 + 470, 0, $soft ); } break;
		case 2: for ( $x = 90; $x <= 610; $x += 130 ) { for ( $y = 90; $y <= 610; $y += 130 ) { imagefilledellipse( $im, $x, $y, 46, 46, $white ); } } imagefilledellipse( $im, 350, 350, 120, 120, $soft ); break;
		case 3: imagefilledrectangle( $im, 175, 175, 525, 525, $white ); imagefilledrectangle( $im, 240, 240, 460, 460, $soft ); break;
		case 4: imagesetthickness( $im, 22 ); imageline( $im, 350, 160, 180, 540, $white ); imageline( $im, 180, 540, 520, 540, $white ); imageline( $im, 520, 540, 350, 160, $white ); imagefilledellipse( $im, 350, 420, 56, 56, $soft ); break;
		case 5: imagesetthickness( $im, 20 ); imagearc( $im, 350, 700, 500, 560, 200, 340, $white ); imagearc( $im, 350, 760, 360, 420, 200, 340, $soft ); imagefilledellipse( $im, 350, 230, 90, 90, $white ); break;
	}
	$tmp = tempnam( sys_get_temp_dir(), 'bzimg' ) . '.png';
	imagepng( $im, $tmp, 7 );
	imagedestroy( $im );
	$upload = wp_upload_bits( $name, null, file_get_contents( $tmp ) );
	@unlink( $tmp );
	if ( ! empty( $upload['error'] ) ) { return 0; }
	$att = wp_insert_attachment( array( 'post_mime_type' => 'image/png', 'post_title' => $name, 'post_status' => 'inherit' ), $upload['file'] );
	wp_update_attachment_metadata( $att, wp_generate_attachment_metadata( $att, $upload['file'] ) );
	return $att;
}

function bz_media_missing( $attachment_id ) {
	if ( ! $attachment_id ) { return true; }
	$file = get_attached_file( $attachment_id );
	return ! $file || ! file_exists( $file );
}

/* ---------- نقشه رنگ محصولات (همان دمو) ---------- */
$map = array(
	'BZ-PHONE-001' => array( array( 15, 118, 110 ), array( 4, 47, 46 ), 0 ),
	'BZ-PHONE-002' => array( array( 51, 65, 85 ), array( 15, 23, 42 ), 1 ),
	'BZ-LAP-001'   => array( array( 37, 99, 235 ), array( 15, 23, 42 ), 2 ),
	'BZ-AUD-001'   => array( array( 6, 182, 212 ), array( 8, 47, 73 ), 5 ),
	'BZ-HOME-001'  => array( array( 234, 179, 8 ), array( 146, 64, 14 ), 4 ),
	'BZ-MOB-001'   => array( array( 132, 204, 22 ), array( 20, 83, 45 ), 2 ),
	'BZ-WAT-001'   => array( array( 225, 29, 72 ), array( 76, 5, 25 ), 0 ),
	'BZ-AUD-002'   => array( array( 100, 116, 139 ), array( 30, 41, 59 ), 3 ),
	'BZ-AUD-003'   => array( array( 71, 85, 105 ), array( 15, 23, 42 ), 5 ),
	'BZ-ACC-001'   => array( array( 120, 113, 108 ), array( 41, 37, 36 ), 4 ),
	'BZ-CLT-001'   => array( array( 245, 158, 11 ), array( 120, 53, 15 ), 1 ),
	'BZ-CLT-002'   => array( array( 22, 163, 74 ), array( 6, 78, 59 ), 0 ),
	'BZ-DIG-001'   => array( array( 139, 92, 246 ), array( 76, 29, 149 ), 3 ),
	'BZ-DIG-002'   => array( array( 236, 72, 153 ), array( 131, 24, 67 ), 2 ),
	'BZ-DIG-003'   => array( array( 79, 70, 229 ), array( 30, 27, 75 ), 1 ),
	'BZ-EXT-001'   => array( array( 168, 85, 247 ), array( 59, 7, 100 ), 4 ),
	'BZ-GRP-001'   => array( array( 100, 116, 139 ), array( 30, 41, 59 ), 2 ),
);

$fixed = 0;
foreach ( $map as $sku => $conf ) {
	$pid = wc_get_product_id_by_sku( $sku );
	if ( ! $pid ) { continue; }
	$product = wc_get_product( $pid );
	if ( bz_media_missing( $product->get_image_id() ) ) {
		$img = bz_placeholder( 'bazaar-' . $sku . '.png', $conf[0], $conf[1], $conf[2] );
		$product->set_image_id( $img );
		$product->save();
		$fixed++;
	}
}
echo "تصاویر محصولات بازسازی‌شده: $fixed\n";

/* ---------- فایل‌های دانلودی ---------- */
function bz_demo_download( $filename, $content ) {
	$upload = wp_upload_bits( $filename, null, $content );
	if ( ! empty( $upload['error'] ) ) { return ''; }
	return $upload['url'];
}

$ebook_pdf = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 595 842]/Contents 4 0 R/Resources<</Font<</F1 5 0 R>>>>>>endobj\n4 0 obj<</Length 90>>stream\nBT/F1 24 Tf 72 770 Td(Wordpress Ebook - Bazaar Demo)Tj ET\nBT/F1 12 Tf 72 740 Td(Bazar Shop Sample Downloadable Product)Tj ET\nendstream\nendobj\n5 0 obj<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>endobj\ntrailer<</Root 1 0 R>>\n";

$icon_zip_tmp = tempnam( sys_get_temp_dir(), 'bzzip' ) . '.zip';
if ( class_exists( 'ZipArchive' ) ) {
	$zip = new ZipArchive();
	$zip->open( $icon_zip_tmp, ZipArchive::CREATE );
	for ( $i = 1; $i <= 5; $i++ ) {
		$zip->addFromString( "icon-$i.svg", '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#0f766e" stroke-width="2"><circle cx="12" cy="12" r="' . ( 4 + $i ) . '"/></svg>' );
	}
	$zip->close();
	$icon_zip_url = bz_demo_download( 'bazaar-icon-pack.zip', file_get_contents( $icon_zip_tmp ) );
	@unlink( $icon_zip_tmp );
} else {
	$icon_zip_url = bz_demo_download( 'bazaar-icon-pack.txt', 'Bazaar demo icon pack' );
}

$digital_files = array(
	'BZ-DIG-001' => bz_demo_download( 'wordpress-ebook-sample.pdf', $ebook_pdf ),
	'BZ-DIG-002' => $icon_zip_url,
	'BZ-DIG-003' => bz_demo_download( 'bazaar-theme-sample.zip', 'Bazaar demo theme package' ),
);

$dl_fixed = 0;
foreach ( $digital_files as $sku => $file_url ) {
	if ( ! $file_url ) { continue; }
	$pid = wc_get_product_id_by_sku( $sku );
	if ( ! $pid ) { continue; }
	$product = wc_get_product( $pid );
	if ( ! $product->is_downloadable() ) { continue; }
	$dl = new WC_Product_Download();
	$dl->set_id( md5( $file_url ) );
	$dl->set_name( 'دانلود فایل' );
	$dl->set_file( $file_url );
	$product->set_downloads( array( $dl ) );
	$product->save();
	$dl_fixed++;
}
echo "فایل‌های دانلودی بازسازی‌شده: $dl_fixed\n";

/* ---------- تصاویر وبلاگ ---------- */
$post_colors = array(
	1 => array( array( 15, 118, 110 ), array( 4, 47, 46 ), 1 ),
	2 => array( array( 245, 158, 11 ), array( 146, 64, 14 ), 2 ),
	3 => array( array( 139, 92, 246 ), array( 76, 29, 149 ), 3 ),
);
$posts_fixed = 0;
$query = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 20 ) );
$i = 0;
while ( $query->have_posts() ) {
	$query->the_post();
	$i++;
	if ( bz_media_missing( get_post_thumbnail_id() ) ) {
		$conf = $post_colors[ $i % 3 + 1 ];
		$img  = bz_placeholder( 'bazaar-post-' . $i . '.png', $conf[0], $conf[1], $conf[2] );
		set_post_thumbnail( get_the_ID(), $img );
		$posts_fixed++;
	}
}
wp_reset_postdata();
echo "تصاویر وبلاگ بازسازی‌شده: $posts_fixed\n";
echo "=== تمام شد ===\n";
