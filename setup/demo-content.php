<?php
/**
 * محتوای نمونه فروشگاه «بازار»
 * اجرا:  wp eval-file setup/demo-content.php
 *
 * تمام انواع محصولات ووکامرس را می‌سازد:
 *   ساده | متغیر | دانلودی | خارجی (همکاری در فروش) | گروهی
 * به‌همراه دسته‌بندی‌ها، تصاویر، نظرات، برگه‌ها، نوشته‌ها و منوها.
 *
 * @package Bazaar
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
require_once ABSPATH . 'wp-admin/includes/image.php';

echo "=== ساخت محتوای نمونه بازار ===\n";

/* ---------------------------------------------------------
 * ۱) تصاویر placeholder با GD (بدون نیاز به اینترنت)
 * ------------------------------------------------------- */
function bz_placeholder( $name, $c1, $c2, $style = 0 ) {
	$W = 700; $H = 700;
	$im = imagecreatetruecolor( $W, $H );

	for ( $y = 0; $y < $H; $y++ ) {
		$t  = $y / $H;
		$r  = (int) ( $c1[0] + ( $c2[0] - $c1[0] ) * $t );
		$g  = (int) ( $c1[1] + ( $c2[1] - $c1[1] ) * $t );
		$b  = (int) ( $c1[2] + ( $c2[2] - $c1[2] ) * $t );
		imageline( $im, 0, $y, $W, $y, imagecolorallocate( $im, $r, $g, $b ) );
	}

	$white = imagecolorallocatealpha( $im, 255, 255, 255, 78 );
	$soft  = imagecolorallocatealpha( $im, 255, 255, 255, 108 );

	switch ( $style % 6 ) {
		case 0: /* حلقه‌ها */
			imagesetthickness( $im, 26 );
			imageellipse( $im, 350, 330, 300, 300, $white );
			imageellipse( $im, 350, 330, 180, 180, $soft );
			imagefilledellipse( $im, 350, 330, 66, 66, $white );
			break;
		case 1: /* نوارهای مورب */
			imagesetthickness( $im, 34 );
			for ( $i = -2; $i < 7; $i++ ) { imageline( $im, $i * 140, 700, $i * 140 + 400, 0, $white ); }
			imagesetthickness( $im, 10 );
			for ( $i = -2; $i < 7; $i++ ) { imageline( $im, $i * 140 + 70, 700, $i * 140 + 470, 0, $soft ); }
			break;
		case 2: /* شبکه نقطه */
			for ( $x = 90; $x <= 610; $x += 130 ) {
				for ( $y = 90; $y <= 610; $y += 130 ) { imagefilledellipse( $im, $x, $y, 46, 46, $white ); }
			}
			imagefilledellipse( $im, 350, 350, 120, 120, $soft );
			break;
		case 3: /* مربع گردگوشه */
			imagefilledrectangle( $im, 175, 175, 525, 525, $white );
			imagefilledrectangle( $im, 240, 240, 460, 460, $soft );
			break;
		case 4: /* مثلث */
			imagesetthickness( $im, 22 );
			imageline( $im, 350, 160, 180, 540, $white );
			imageline( $im, 180, 540, 520, 540, $white );
			imageline( $im, 520, 540, 350, 160, $white );
			imagefilledellipse( $im, 350, 420, 56, 56, $soft );
			break;
		case 5: /* موج */
			imagesetthickness( $im, 20 );
			imagearc( $im, 350, 700, 500, 560, 200, 340, $white );
			imagearc( $im, 350, 760, 360, 420, 200, 340, $soft );
			imagefilledellipse( $im, 350, 230, 90, 90, $white );
			break;
	}

	$tmp = tempnam( sys_get_temp_dir(), 'bzimg' ) . '.png';
	imagepng( $im, $tmp, 7 );
	imagedestroy( $im );

	$upload = wp_upload_bits( $name, null, file_get_contents( $tmp ) );
	@unlink( $tmp );
	if ( ! empty( $upload['error'] ) ) { return 0; }

	$att = wp_insert_attachment( array(
		'post_mime_type' => 'image/png',
		'post_title'     => $name,
		'post_status'    => 'inherit',
	), $upload['file'] );
	wp_update_attachment_metadata( $att, wp_generate_attachment_metadata( $att, $upload['file'] ) );
	return $att;
}

/* ---------------------------------------------------------
 * ۲) دسته‌بندی‌ها
 * ------------------------------------------------------- */
$cats = array();
foreach ( array(
	'mobile'   => 'موبایل و تبلت',
	'laptop'   => 'لپ‌تاپ و کامپیوتر',
	'clothing' => 'پوشاک',
	'home'     => 'لوازم خانگی',
	'digital'  => 'محصولات دانلودی',
	'beauty'   => 'زیبایی و سلامت',
) as $slug => $name ) {
	$term = term_exists( $slug, 'product_cat' );
	if ( ! $term ) { $term = wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) ); }
	$cats[ $slug ] = (int) ( is_array( $term ) ? $term['term_id'] : $term );
}
echo "دسته‌بندی‌ها: " . count( $cats ) . "\n";

/* ---------------------------------------------------------
 * ۳) فایل‌های دانلودی نمونه
 * ------------------------------------------------------- */
function bz_demo_download( $filename, $content ) {
	$upload = wp_upload_bits( $filename, null, $content );
	if ( ! empty( $upload['error'] ) ) { return ''; }
	return $upload['url'];
}

/* تأیید پوشه uploads به‌عنوان دایرکتوری مجاز دانلود (الزامی از ووکامرس ۵.۵+) */
if ( class_exists( '\Automattic\WooCommerce\Internal\ProductDownloads\ApprovedDirectories\Register' ) ) {
	$bz_reg = wc_get_container()->get( \Automattic\WooCommerce\Internal\ProductDownloads\ApprovedDirectories\Register::class );
	$bz_up  = wp_upload_dir();
	$bz_reg->add_approved_directory( $bz_up['baseurl'] );
	$bz_reg->add_approved_directory( $bz_up['basedir'] );
}

$ebook_pdf = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 595 842]/Contents 4 0 R/Resources<</Font<</F1 5 0 R>>>>>>endobj\n4 0 obj<</Length 90>>stream\nBT/F1 24 Tf 72 770 Td(Wordpress Ebook - Bazaar Demo)Tj ET\nBT/F1 12 Tf 72 740 Td(Bazar Shop Sample Downloadable Product)Tj ET\nendstream\nendobj\n5 0 obj<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>endobj\ntrailer<</Root 1 0 R>>\n";

/* پکیج آیکون: زیپ شامل چند SVG */
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
$ebook_url = bz_demo_download( 'wordpress-ebook-sample.pdf', $ebook_pdf );
$theme_zip_url = bz_demo_download( 'bazaar-theme-sample.zip', 'Bazaar demo theme package' );

/* ---------------------------------------------------------
 * ۴) ساخت محصولات (idempotent)
 * ------------------------------------------------------- */
function bz_exists( $sku ) {
	$id = wc_get_product_id_by_sku( $sku );
	return $id ? wc_get_product( $id ) : null;
}

function bz_attach_image( $product, $key, $c1, $c2, $style, $gallery_more = array() ) {
	$img = bz_placeholder( 'bazaar-' . $key . '.png', $c1, $c2, $style );
	$product->set_image_id( $img );
	if ( $gallery_more ) { $product->set_gallery_image_ids( $gallery_more ); }
	return $img;
}

$products = array();

/* --- ساده (Simple) --- */
$map = array(
	array( 'sku' => 'BZ-PHONE-001', 'name' => 'گوشی موبایل سامسونگ Galaxy A55 ظرفیت ۲۵۶ گیگابایت', 'cat' => 'mobile', 'regular' => 24900000, 'sale' => 21900000, 'stock' => 15, 'featured' => true, 'c1' => array( 15, 118, 110 ), 'c2' => array( 4, 47, 46 ), 'style' => 0,
		'desc' => 'گوشی میان‌رده محبوب سامسونگ با نمایشگر Super AMOLED ۶.۶ اینچی، دوربین ۵۰ مگاپیکسلی و باتری ۵۰۰۰ میلی‌آمپری. مناسب برای عکاری، بازی و استفاده روزمره.',
		'short' => 'نمایشگر ۱۲۰Hz | دوربین ۵۰MP | باتری ۵۰۰۰mAh | گارانتی ۱۸ ماهه' ),
	array( 'sku' => 'BZ-PHONE-002', 'name' => 'گوشی موبایل آیفون ۱۵ پرو ظرفیت ۲۵۶ گیگابایت', 'cat' => 'mobile', 'regular' => 89500000, 'sale' => 0, 'stock' => 8, 'featured' => true, 'c1' => array( 51, 65, 85 ), 'c2' => array( 15, 23, 42 ), 'style' => 1,
		'desc' => 'آیفون ۱۵ پرو با بدنه تیتانیومی، تراشه A17 Pro و端口 USB-C. عالی برای فیلم‌برداری حرفه‌ای و کارهای سنگین.',
		'short' => 'تراشه A17 Pro | دوربین ۴۸MP | بدنه تیتانیوم | اورجینال' ),
	array( 'sku' => 'BZ-LAP-001', 'name' => 'لپ‌تاپ ایسوس VivoBook 15 مدل X1504 — Core i5 / 16GB / 512SSD', 'cat' => 'laptop', 'regular' => 62000000, 'sale' => 0, 'stock' => 6, 'featured' => true, 'c1' => array( 37, 99, 235 ), 'c2' => array( 15, 23, 42 ), 'style' => 2,
		'desc' => 'لپ‌تاپ کاربردی برای کارهای اداری، دانشجویی و برنامه‌نویسی سبک با صفحه‌نمایش ۱۵.۶ اینچی Full HD و صفحه‌کلید نومپد.',
		'short' => 'Core i5 نسل ۱۳ | رم ۱۶GB | حافظه ۵۱۲GB SSD | ویندوز ۱۱' ),
	array( 'sku' => 'BZ-AUD-001', 'name' => 'هدفون بی‌سیم پرو با نویز کنسلینگ فعال', 'cat' => 'laptop', 'regular' => 4200000, 'sale' => 3350000, 'stock' => 32, 'featured' => true, 'c1' => array( 6, 182, 212 ), 'c2' => array( 8, 47, 73 ), 'style' => 5,
		'desc' => 'هدفون بلوتوثی با حذف نویز فعال (ANC)، ۴۰ ساعت پخش موسیقی و میکروفون هوشمند برای مکالمات شفاف.',
		'short' => 'ANC فعال | ۴۰ ساعت باتری | بلوتوث ۵.۳ | تاشو' ),
	array( 'sku' => 'BZ-HOME-001', 'name' => 'جاروبرقی رباتیک هوشمند با نقشه‌برداری لیزری', 'cat' => 'home', 'regular' => 18700000, 'sale' => 0, 'stock' => 10, 'featured' => true, 'c1' => array( 234, 179, 8 ), 'c2' => array( 146, 64, 14 ), 'style' => 4,
		'desc' => 'ربات جاروبرقی با لیدار ۳۶۰ درجه، مخزن دوگانه (جارو + شست‌وشو) و کنترل از طریق اپلیکیشن و دستیار صوتی.',
		'short' => 'نقشه‌برداری لیدار | مخزن دوگانه | اپلیکیشن هوشمند' ),
	array( 'sku' => 'BZ-MOB-001', 'name' => 'پاوربانک ۲۰۰۰۰ میلی‌آمپر با شارژ سریع ۶۵ وات', 'cat' => 'mobile', 'regular' => 1150000, 'sale' => 0, 'stock' => 45, 'featured' => true, 'c1' => array( 132, 204, 22 ), 'c2' => array( 20, 83, 45 ), 'style' => 2,
		'desc' => 'پاوربانک پرظرفیت با دو خروجی USB-C و USB-A، نمایشگر دیجیتال و قابلیت شارژ لپ‌تاپ.',
		'short' => '۲۰۰۰۰mAh | شارژ سریع ۶۵W | نمایشگر دیجیتال' ),
	array( 'sku' => 'BZ-WAT-001', 'name' => 'ساعت هوشمند Ultra با نمایشگر AMOLED و GPS دوفرکانسی', 'cat' => 'mobile', 'regular' => 12500000, 'sale' => 10900000, 'stock' => 18, 'featured' => true, 'c1' => array( 225, 29, 72 ), 'c2' => array( 76, 5, 25 ), 'style' => 0,
		'desc' => 'ساعت هوشمند ورزشی با بدنه فلزی، بیش از ۱۵۰ حالت ورزشی، مقاوم در برابر آب و باتری ۱۴ روزه.',
		'short' => 'AMOLED | GPS دوفرکانسی | مقاوم ۵ATM | باتری ۱۴ روزه' ),
	array( 'sku' => 'BZ-AUD-002', 'name' => 'میکروفون USB پودکاستی کاندنسور', 'cat' => 'laptop', 'regular' => 3800000, 'sale' => 0, 'stock' => 12, 'featured' => false, 'c1' => array( 100, 116, 139 ), 'c2' => array( 30, 41, 59 ), 'style' => 3,
		'desc' => 'میکروفون حرفه‌ای ضبط پادکست با الگوی کاردیوئید، پایه ضدلرزش و دکمه قطع صدا.',
		'short' => 'کاردیوئید | نرخ ۲۴bit/96kHz | پایه ضدلرزش' ),
	array( 'sku' => 'BZ-AUD-003', 'name' => 'هدفون استودیویی مانیتورینگ حرفه‌ای', 'cat' => 'laptop', 'regular' => 5600000, 'sale' => 0, 'stock' => 9, 'featured' => false, 'c1' => array( 71, 85, 105 ), 'c2' => array( 15, 23, 42 ), 'style' => 5,
		'desc' => 'هدفون سیمی مانیتورینگ با پاسخ فرکانسی تخت برای میکس و مسترینگ حرفه‌ای.',
		'short' => 'پاسخ تخت | درایور ۴۵mm | کابل قابل تعویض' ),
	array( 'sku' => 'BZ-ACC-001', 'name' => 'سه‌پایه دوربین آلومینیومی حرفه‌ای ۱۷۰ سانتی‌متر', 'cat' => 'laptop', 'regular' => 1450000, 'sale' => 0, 'stock' => 20, 'featured' => false, 'c1' => array( 120, 113, 108 ), 'c2' => array( 41, 37, 36 ), 'style' => 4,
		'desc' => 'سه‌پایه سبک و مقاوم با سر کروی سه‌جهته و کیف حمل.',
		'short' => 'ارتفاع ۱۷۰cm | وزن ۱.۲kg | سر کروی' ),
);

foreach ( $map as $p ) {
	if ( bz_exists( $p['sku'] ) ) { $products[ $p['sku'] ] = bz_exists( $p['sku'] ); continue; }
	$obj = new WC_Product_Simple();
	$obj->set_sku( $p['sku'] );
	$obj->set_name( $p['name'] );
	$obj->set_description( $p['desc'] );
	$obj->set_short_description( $p['short'] );
	$obj->set_regular_price( $p['regular'] );
	if ( $p['sale'] ) { $obj->set_sale_price( $p['sale'] ); }
	$obj->set_manage_stock( true );
	$obj->set_stock_quantity( $p['stock'] );
	$obj->set_category_ids( array( $cats[ $p['cat'] ] ) );
	$obj->set_featured( $p['featured'] );
	$obj->set_reviews_allowed( true );
	bz_attach_image( $obj, $p['sku'], $p['c1'], $p['c2'], $p['style'] );
	$obj->save();
	$products[ $p['sku'] ] = $obj;
}
echo "محصولات ساده: " . count( $map ) . "\n";

/* --- متغیر (Variable) — تی‌شرت با سایز و رنگ --- */
if ( ! bz_exists( 'BZ-CLT-001' ) ) {
	$v = new WC_Product_Variable();
	$v->set_sku( 'BZ-CLT-001' );
	$v->set_name( 'تی‌شرت نخی مردانه نیمه‌یقه مدل City' );
	$v->set_description( 'تی‌شرت نخی درجه یک با دوخت تمیز و رنگ‌بندی متنوع. این محصول به‌صورت متغیر ارائه می‌شود و می‌توانید سایز و رنگ دلخواه خود را انتخاب کنید.' );
	$v->set_short_description( 'نخ ۱۰۰٪ پنبه | سایزهای S تا XL | سه رنگ' );
	$v->set_category_ids( array( $cats['clothing'] ) );
	$v->set_reviews_allowed( true );

	$attr_size = new WC_Product_Attribute();
	$attr_size->set_id( 0 );
	$attr_size->set_name( 'سایز' );
	$attr_size->set_options( array( 'S', 'M', 'L', 'XL' ) );
	$attr_size->set_position( 0 );
	$attr_size->set_visible( true );
	$attr_size->set_variation( true );

	$attr_color = new WC_Product_Attribute();
	$attr_color->set_id( 0 );
	$attr_color->set_name( 'رنگ' );
	$attr_color->set_options( array( 'مشکی', 'سفید', 'سرمه‌ای' ) );
	$attr_color->set_position( 1 );
	$attr_color->set_visible( true );
	$attr_color->set_variation( true );

	$v->set_attributes( array( $attr_size, $attr_color ) );
	bz_attach_image( $v, 'BZ-CLT-001', array( 245, 158, 11 ), array( 120, 53, 15 ), 1 );
	$v->save();

	$sizes = array( 'S' => 330000, 'M' => 350000, 'L' => 350000, 'XL' => 390000 );
	foreach ( $sizes as $size => $price ) {
		$var = new WC_Product_Variation();
		$var->set_parent_id( $v->get_id() );
		$var->set_attributes( array( 'سایز' => $size, 'رنگ' => 'مشکی' ) );
		$var->set_regular_price( $price );
		$var->set_manage_stock( true );
		$var->set_stock_quantity( 10 );
		$var->save();
	}
	$var = new WC_Product_Variation();
	$var->set_parent_id( $v->get_id() );
	$var->set_attributes( array( 'سایز' => 'M', 'رنگ' => 'سفید' ) );
	$var->set_regular_price( 340000 );
	$var->set_manage_stock( true );
	$var->set_stock_quantity( 6 );
	$var->save();
	WC_Product_Variable::sync( $v->get_id() );
	$products['BZ-CLT-001'] = $v;
	echo "محصول متغیر: تی‌شرت (۵ واریانت) ✓\n";
}

/* --- متغیر — کفش ورزشی --- */
if ( ! bz_exists( 'BZ-CLT-002' ) ) {
	$v2 = new WC_Product_Variable();
	$v2->set_sku( 'BZ-CLT-002' );
	$v2->set_name( 'کفش ورزشی رانینگ مدل Air Flex' );
	$v2->set_description( 'کفش دویدن سبک با زیره فوم تزریقی و رویه مش تنفس‌پذیر؛ انتخاب سایز از ۴۰ تا ۴۵.' );
	$v2->set_short_description( 'زیره فوم | وزن ۲۵۰ گرم | سایز ۴۰ تا ۴۵' );
	$v2->set_category_ids( array( $cats['clothing'] ) );
	$attr = new WC_Product_Attribute();
	$attr->set_id( 0 );
	$attr->set_name( 'سایز' );
	$attr->set_options( array( '۴۰', '۴۱', '۴۲', '۴۳', '۴۴', '۴۵' ) );
	$attr->set_position( 0 );
	$attr->set_visible( true );
	$attr->set_variation( true );
	$v2->set_attributes( array( $attr ) );
	bz_attach_image( $v2, 'BZ-CLT-002', array( 22, 163, 74 ), array( 6, 78, 59 ), 0 );
	$v2->save();

	foreach ( array( '۴۰' => 1850000, '۴۱' => 1850000, '۴۲' => 1890000, '۴۳' => 1890000, '۴۴' => 1950000, '۴۵' => 1950000 ) as $size => $price ) {
		$var = new WC_Product_Variation();
		$var->set_parent_id( $v2->get_id() );
		$var->set_attributes( array( 'سایز' => $size ) );
		$var->set_regular_price( $price );
		$var->set_manage_stock( true );
		$var->set_stock_quantity( 7 );
		$var->save();
	}
	WC_Product_Variable::sync( $v2->get_id() );
	$products['BZ-CLT-002'] = $v2;
	echo "محصول متغیر: کفش (۶ واریانت) ✓\n";
}

/* --- دانلودی (Downloadable / Digital) --- */
$digital = array(
	array( 'sku' => 'BZ-DIG-001', 'name' => 'کتاب الکترونیکی جامع آموزش وردپرس (PDF)', 'price' => 250000, 'file' => $ebook_url, 'c1' => array( 139, 92, 246 ), 'c2' => array( 76, 29, 149 ), 'style' => 3, 'featured' => false,
		'desc' => 'کتاب PDF ۳۲۰ صفحه‌ای آموزش وردپرس از صفر تا حرفه‌ای؛ شامل نصب، قالب، افزونه، سئو و امنیت. بلافاصله پس از خرید لینک دانلود فعال می‌شود.',
		'short' => '۳۲۰ صفحه PDF | دانلود آنی | به‌روزرسانی رایگان' ),
	array( 'sku' => 'BZ-DIG-002', 'name' => 'پکیج ۵۰۰ آیکون UI مدرن (SVG + PNG)', 'price' => 390000, 'file' => $icon_zip_url, 'c1' => array( 236, 72, 153 ), 'c2' => array( 131, 24, 67 ), 'style' => 2, 'featured' => false,
		'desc' => 'مجموعه آیکون‌های وکتور برای طراحی رابط کاربری؛ شامل فرمت‌های SVG و PNG با مجوز استفاده تجاری.',
		'short' => '۵۰۰ آیکون | SVG + PNG | مجوز تجاری' ),
	array( 'sku' => 'BZ-DIG-003', 'name' => 'قالب وردپرس فروشگاهی حرفه‌ای (نسخه کامل)', 'price' => 1290000, 'sale' => 890000, 'file' => $theme_zip_url, 'c1' => array( 79, 70, 229 ), 'c2' => array( 30, 27, 75 ), 'style' => 1, 'featured' => true,
		'desc' => 'قالب وردپرس فروشگاهی سازگار با ووکامرس به‌همراه پشتیبانی ۶ ماهه و به‌روزرسانی مادام‌العمر. دانلود فوری پس از پرداخت.',
		'short' => 'سازگار با ووکامرس | پشتیبانی ۶ ماهه | دانلود آنی' ),
);
foreach ( $digital as $d ) {
	if ( bz_exists( $d['sku'] ) ) { continue; }
	$obj = new WC_Product_Simple();
	$obj->set_sku( $d['sku'] );
	$obj->set_name( $d['name'] );
	$obj->set_description( $d['desc'] );
	$obj->set_short_description( $d['short'] );
	$obj->set_regular_price( $d['price'] );
	if ( ! empty( $d['sale'] ) ) { $obj->set_sale_price( $d['sale'] ); }
	$obj->set_virtual( true );
	$obj->set_downloadable( true );
	$dl = new WC_Product_Download();
	$dl->set_id( md5( $d['file'] ) );
	$dl->set_name( 'دانلود فایل' );
	$dl->set_file( $d['file'] );
	$obj->set_downloads( array( $dl ) );
	$obj->set_category_ids( array( $cats['digital'] ) );
	$obj->set_featured( $d['featured'] );
	bz_attach_image( $obj, $d['sku'], $d['c1'], $d['c2'], $d['style'] );
	$obj->save();
	echo "محصول دانلودی: {$d['name']} ✓\n";
}

/* --- خارجی / همکاری در فروش (External / Affiliate) --- */
if ( ! bz_exists( 'BZ-EXT-001' ) ) {
	$e = new WC_Product_External();
	$e->set_sku( 'BZ-EXT-001' );
	$e->set_name( 'دوربین آینه‌ای حرفه‌ای Alpha (خرید از فروشنده خارجی)' );
	$e->set_description( 'این محصول به‌صورت همکاری در فروش عرضه می‌شود؛ با کلیک روی دکمه خرید، به صفحه فروشنده منتقل می‌شوید و خرید را در سایت او کامل می‌کنید. مناسب برای فروش بدون انبار و بدون ارسال.' );
	$e->set_short_description( 'فرصت خرید مستقیم از فروشنده معتبر خارجی | ارسال بین‌المللی' );
	$e->set_regular_price( 145000000 );
	$e->set_category_ids( array( $cats['laptop'] ) );
	$e->set_product_url( 'https://www.sony.com/electronics/cameras' );
	$e->set_button_text( 'خرید از فروشنده' );
	bz_attach_image( $e, 'BZ-EXT-001', array( 168, 85, 247 ), array( 59, 7, 100 ), 4 );
	$e->save();
	echo "محصول خارجی (افیلیت): دوربین ✓\n";
}

/* --- گروهی (Grouped) --- */
if ( ! bz_exists( 'BZ-GRP-001' ) ) {
	$g = new WC_Product_Grouped();
	$g->set_sku( 'BZ-GRP-001' );
	$g->set_name( 'پکیج کامل استودیو پادکست (۳ محصول گروهی)' );
	$g->set_description( 'پکیج گروهی شامل میکروفون پودکاستی، هدفون مانیتورینگ و سه‌پایه حرفه‌ای؛ می‌توانید هر محصول را جداگانه یا به‌صورت پکیج کامل تهیه کنید.' );
	$g->set_short_description( 'میکروفون + هدفون + سه‌پایه | مناسب شروع پادکست حرفه‌ای' );
	$g->set_category_ids( array( $cats['laptop'] ) );
	$g->set_children( array(
		$products['BZ-AUD-002']->get_id(),
		$products['BZ-AUD-003']->get_id(),
		$products['BZ-ACC-001']->get_id(),
	) );
	bz_attach_image( $g, 'BZ-GRP-001', array( 100, 116, 139 ), array( 30, 41, 59 ), 2 );
	$g->save();
	echo "محصول گروهی: پکیج استودیو ✓\n";
}

/* ---------------------------------------------------------
 * ۵) نظرات و امتیاز نمونه
 * ------------------------------------------------------- */
function bz_review( $product_id, $author, $rating, $text ) {
	if ( get_comments( array( 'post_id' => $product_id, 'count' => true ) ) ) { return; }
	$cid = wp_insert_comment( array(
		'comment_post_ID'      => $product_id,
		'comment_author'       => $author,
		'comment_author_email' => sanitize_title( $author ) . '@example.com',
		'comment_content'      => $text,
		'comment_type'         => 'review',
		'comment_approved'     => 1,
		'comment_date'         => current_time( 'mysql' ),
	) );
	update_comment_meta( $cid, 'rating', $rating );
	update_comment_meta( $cid, 'verified', 1 );
}

if ( isset( $products['BZ-AUD-001'] ) ) {
	$h = $products['BZ-AUD-001']->get_id();
	bz_review( $h, 'سارا محمدی', 5, 'کیفیت صدا فوق‌العاده و باتری واقعاً طولانی است. ارسال هم سریع بود. ممنون از بازار 🙏' );
	bz_review( $h, 'امیر رضایی', 4, 'نویز کنسلینگ خوبی داره ولی برای بلندگو مکالمه کمی ضعیفه. در کل به قیمتش می‌ارزد.' );
}
if ( isset( $products['BZ-PHONE-001'] ) ) {
	bz_review( $products['BZ-PHONE-001']->get_id(), 'محمد کریمی', 5, 'دقیقاً همون چیزی بود که توی توضیحات نوشته شده. جعبه پلمب و گارانتی معتبر. خرید دومم از این فروشگاهه!' );
}
if ( isset( $products['BZ-CLT-001'] ) ) {
	bz_review( $products['BZ-CLT-001']->get_id(), 'نگار احمدی', 4, 'جنس نخ خوش‌دست و سایزبندی درسته. رنگ سرمه‌ای خیلی قشنگه.' );
}
echo "نظرات نمونه ✓\n";

/* ---------------------------------------------------------
 * ۶) برگه‌ها و نوشته‌های وبلاگ
 * ------------------------------------------------------- */
function bz_page( $slug, $title, $content ) {
	$page = get_page_by_path( $slug );
	if ( $page ) { return $page->ID; }
	return wp_insert_post( array(
		'post_type'    => 'page',
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_content' => $content,
		'post_status'  => 'publish',
	) );
}

bz_page( 'about', 'درباره بازار', '<p>بازار یک فروشگاه اینترنتی کامل است که با وردپرس و ووکامرس ساخته شده است. ما با تکیه بر سال‌ها تجربه، بهترین کالاها را با قیمت منصفانه و ارسال سریع به دست شما می‌رسانیم.</p><h2>چرا بازار؟</h2><ul><li>ضمانت اصالت و سلامت فیزیکی کالا</li><li>پشتیبانی ۲۴ ساعته در ۷ روز هفته</li><li>امکان بازگشت کالا تا ۷ روز</li><li>پشتیبانی از انواع پرداخت و خرید دانلودی</li></ul>' );
bz_page( 'contact', 'تماس با ما', '<p>برای ارتباط با تیم پشتیبانی بازار از راه‌های زیر استفاده کنید:</p><ul><li>تلفن: ۰۲۱-۹۱۰۰۰۰۰۰ (۲۴ ساعته)</li><li>ایمیل: info@bazaar-shop.ir</li><li>آدرس: تهران، خیابان ولیعصر، برج بازار، طبقه ۷</li></ul>' );
bz_page( 'faq', 'سوالات متداول', '<h3>چگونه سفارش خود را ثبت کنم؟</h3><p>کالا را به سبد خرید اضافه کنید و به صفحه پرداخت بروید؛ پس از تکمیل اطلاعات، سفارش شما ثبت می‌شود.</p><h3>محصولات دانلودی چگونه تحویل داده می‌شوند؟</h3><p>بلافاصله پس از پرداخت موفق، لینک دانلود در حساب کاربری و ایمیل شما قرار می‌گیرد.</p><h3>امکان بازگشت کالا وجود دارد؟</h3><p>بله؛ تا ۷ روز پس از تحویل، در صورت سالم بودن بسته‌بندی، کالا قابل بازگشت است.</p>' );
bz_page( 'terms', 'قوانین و مقررات', '<p>استفاده از فروشگاه بازار به معنای پذیرش قوانین آن است. تمام قیمت‌ها به تومان و شامل مالیات بر ارزش افزوده است.</p>' );
$blog_page = bz_page( 'blog', 'وبلاگ', '' );
update_option( 'page_for_posts', $blog_page );
update_option( 'show_on_front', 'posts' );

$posts = array(
	array( 'راهنمای کامل خرید موبایل؛ چک‌لیست ۱۰ موردی قبل از خرید', 'خرید موبایل نو یکی از پرتکرارترین خریدهای آنلاین است. در این راهنما به نکاتی مانند اصالت کالا، گارانتی، مقایسه مشخصات فنی و انتخاب ظرفیت مناسب می‌پردازیم. اول از همه بودجه خود را مشخص کنید؛ سپس اولویت‌هایتان (دوربین، باتری، نمایشگر) را فهرست کنید. همیشه فروشگاه‌هایی را انتخاب کنید که ضمانت اصالت کالا و امکان بازگشت داشته باشند. در بازار، تمام گوشی‌ها با گارانتی معتبر و بسته‌بندی پلمب ارسال می‌شوند.', array( 15, 118, 110 ), array( 4, 47, 46 ), 1 ),
	array( '۵ نکته طلایی برای خرید آنلاین امن', 'خرید اینترنتی امن نیست سخت است؛ فقط باید چند اصل ساده را رعایت کنید: ۱) آدرس سایت را با دقت تایپ کنید، ۲) قفل کنار آدرس مرورگر را چک کنید، ۳) از رمزهای یکتا استفاده کنید، ۴) فیشینگ را جدی بگیرید و ۵) نظرات خریداران قبلی را بخوانید. رعایت همین پنج نکته ساده، امنیت خرید شما را تضمین می‌کند.', array( 245, 158, 11 ), array( 146, 64, 14 ), 2 ),
	array( 'محصولات دانلودی چیست و چرا خریدشان منطقی است؟', 'محصولات دانلودی (کتاب الکترونیکی، قالب، افزونه، آیکون و…) بدون نیاز به ارسال فیزیکی، بلافاصله پس از پرداخت در دسترس شما قرار می‌گیرند. مزایا: تحویل آنی، حذف هزینه ارسال، دسترسی مادام‌العمر و سازگاری با محیط زیست. فروشگاه بازار از محصولات دانلودی با لینک امن پشتیبانی می‌کند و پس از خرید، فایل‌ها همیشه در بخش «دانلودهای» حساب کاربری شما در دسترس هستند.', array( 139, 92, 246 ), array( 76, 29, 149 ), 3 ),
);
foreach ( $posts as $i => $p ) {
	if ( get_page_by_path( sanitize_title( $p[0] ), OBJECT, 'post' ) ) { continue; }
	$pid = wp_insert_post( array(
		'post_type'    => 'post',
		'post_title'   => $p[0],
		'post_content' => '<p>' . $p[1] . '</p>',
		'post_status'  => 'publish',
		'post_author'  => 1,
	) );
	if ( $pid ) {
		$img = bz_placeholder( 'bazaar-post-' . ( $i + 1 ) . '.png', $p[2], $p[3], $p[4] );
		set_post_thumbnail( $pid, $img );
		wp_set_post_categories( $pid, array( get_option( 'default_category' ) ) );
	}
}
echo "برگه‌ها و نوشته‌ها ✓\n";

/* ---------------------------------------------------------
 * ۷) منوها
 * ------------------------------------------------------- */
$menu_name = 'منوی اصلی';
$menu      = wp_get_nav_menu_object( $menu_name );
if ( ! $menu ) {
	$menu_id = wp_create_nav_menu( $menu_name );

	wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => 'خانه', 'menu-item-url' => home_url( '/' ), 'menu-item-type' => 'custom', 'menu-item-status' => 'publish' ) );
	$shop_page_id = wc_get_page_id( 'shop' );
	wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => 'فروشگاه', 'menu-item-object' => 'page', 'menu-item-object-id' => $shop_page_id, 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) );

	/* زیرمنوی دسته‌بندی‌ها */
	$parent = wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => 'دسته‌بندی‌ها', 'menu-item-url' => '#', 'menu-item-type' => 'custom', 'menu-item-status' => 'publish' ) );
	foreach ( $cats as $slug => $cid ) {
		wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => get_term( $cid )->name, 'menu-item-object' => 'product_cat', 'menu-item-object-id' => $cid, 'menu-item-type' => 'taxonomy', 'menu-item-parent-id' => $parent, 'menu-item-status' => 'publish' ) );
	}

	wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => 'وبلاگ', 'menu-item-object' => 'page', 'menu-item-object-id' => $blog_page, 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) );
	$about_id = get_page_by_path( 'about' )->ID;
	wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => 'درباره ما', 'menu-item-object' => 'page', 'menu-item-object-id' => $about_id, 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) );
	$contact_id = get_page_by_path( 'contact' )->ID;
	wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => 'تماس با ما', 'menu-item-object' => 'page', 'menu-item-object-id' => $contact_id, 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) );

	$locations = get_theme_mod( 'nav_menu_locations' );
	$locations['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
	echo "منوی اصلی ✓\n";
}

$footer_menu_name = 'منوی فوتر';
if ( ! wp_get_nav_menu_object( $footer_menu_name ) ) {
	$fmenu_id = wp_create_nav_menu( $footer_menu_name );
	foreach ( array( 'faq' => 'سوالات متداول', 'about' => 'درباره ما', 'contact' => 'تماس با ما', 'terms' => 'قوانین و مقررات' ) as $slug => $title ) {
		$pid = get_page_by_path( $slug )->ID;
		wp_update_nav_menu_item( $fmenu_id, 0, array( 'menu-item-title' => $title, 'menu-item-object' => 'page', 'menu-item-object-id' => $pid, 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) );
	}
	$locations = get_theme_mod( 'nav_menu_locations' );
	$locations['footer'] = $fmenu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
	echo "منوی فوتر ✓\n";
}

/* ---------------------------------------------------------
 * ۸) عنوان صفحات ووکامرس به فارسی + تنظیمات نهایی
 * ------------------------------------------------------- */
$bz_pages_map = array( 'shop' => 'فروشگاه', 'cart' => 'سبد خرید', 'checkout' => 'پرداخت', 'my-account' => 'حساب کاربری' );
foreach ( $bz_pages_map as $bz_key => $bz_title ) {
	$pid = wc_get_page_id( str_replace( '-', '_', $bz_key ) );
	if ( $pid > 0 ) {
		wp_update_post( array( 'ID' => $pid, 'post_title' => $bz_title, 'post_name' => $bz_key ) );
	}
}

/* استفاده از شورت‌کدهای کلاسیک برای هماهنگی کامل با استایل راست‌چین قالب */
$bz_pid = wc_get_page_id( 'cart' );
if ( $bz_pid > 0 && false === strpos( get_post_field( 'post_content', $bz_pid ), '[woocommerce_cart]' ) ) {
	wp_update_post( array( 'ID' => $bz_pid, 'post_content' => '<!-- wp:shortcode -->[woocommerce_cart]<!-- /wp:shortcode -->' ) );
}
$bz_pid = wc_get_page_id( 'checkout' );
if ( $bz_pid > 0 && false === strpos( get_post_field( 'post_content', $bz_pid ), '[woocommerce_checkout]' ) ) {
	wp_update_post( array( 'ID' => $bz_pid, 'post_content' => '<!-- wp:shortcode -->[woocommerce_checkout]<!-- /wp:shortcode -->' ) );
}

/* آدرس استاندارد حساب کاربری ووکامرس: my-account */
$bz_pid = wc_get_page_id( 'myaccount' );
if ( $bz_pid > 0 && 'my-account' !== get_post_field( 'post_name', $bz_pid ) ) {
	wp_update_post( array( 'ID' => $bz_pid, 'post_name' => 'my-account' ) );
	flush_rewrite_rules();
}
wp_update_post( array( 'ID' => get_option( 'wp_page_for_privacy_policy' ), 'post_title' => 'حریم خصوصی' ) );

update_option( 'blogname', 'بازار' );
update_option( 'blogdescription', 'فروشگاه اینترنتی' );
delete_option( 'woocommerce_onboarding_profile' );
update_option( 'woocommerce_task_list_hidden', 'yes' );
update_option( 'woocommerce_task_list_welcome_modal_dismissed', 'yes' );
update_option( 'woocommerce_allow_tracking', 'no' );
update_option( 'woocommerce_show_marketplace_suggestions', 'no' );

/* غیرفعال‌کردن حالت «به‌زودی» ووکامرس (وگرنه فروشگاه برای مهمان‌ها قفل می‌شود!) */
update_option( 'woocommerce_coming_soon', 'no' );
update_option( 'woocommerce_store_pages_only', 'no' );

echo "\n=== تمام شد! تعداد کل محصولات: " . wp_count_posts( 'product' )->publish . " ===\n";
