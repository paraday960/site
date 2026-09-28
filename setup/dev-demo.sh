#!/usr/bin/env bash
# ============================================================
#  بازسازی محیط دموی فروشگاه «بازار» (تست‌شده روی Debian 13)
#
#  کارهایی که انجام می‌دهد:
#   ۱. نصب PHP + MariaDB + ابزارها
#   ۲. ساخت دیتابیس
#   ۳. نصب WP-CLI
#   ۴. نصب وردپرس فارسی + ووکامرس + بسته‌های زبان
#   ۵. فعال‌سازی قالب بازار (symlink از خود ریپو)
#   ۶. تزریق ۱۷ محصول نمونه از همه انواع + منو + وبلاگ
#   ۷. اجرای سرور روی http://localhost:8080
#
#  اجرا:  bash setup/dev-demo.sh [--no-demo] [--port 8080]
# ============================================================
set -euo pipefail

PORT=8080
WITH_DEMO=1
while [[ $# -gt 0 ]]; do
  case "$1" in
    --port)    PORT="$2"; shift 2 ;;
    --no-demo) WITH_DEMO=0; shift ;;
    *) shift ;;
  esac
done

REPO_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DEMO_DIR="$REPO_DIR/out/wp-demo"
DB_NAME=wp_shop DB_USER=wp_user DB_PASS=wp_pass_123

echo "🛠  بازسازی محیط دموی بازار (پورت $PORT)"

# ---------- ۱) پیش‌نیازها ----------
if ! command -v php >/dev/null; then
  echo "⬇️  نصب PHP و MariaDB..."
  sudo apt-get update -qq
  sudo DEBIAN_FRONTEND=noninteractive apt-get install -y -qq \
    php-cli php-mysql php-xml php-curl php-mbstring php-zip php-gd php-intl \
    mariadb-server mariadb-client unzip curl
fi

# ---------- ۲) دیتابیس ----------
sudo service mariadb start >/dev/null 2>&1 || sudo systemctl start mariadb
sudo mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost'; FLUSH PRIVILEGES;"

# ---------- ۳) WP-CLI ----------
if ! command -v wp >/dev/null; then
  echo "⬇️  نصب WP-CLI..."
  curl -sO https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
  chmod +x wp-cli.phar && sudo mv wp-cli.phar /usr/local/bin/wp
fi

# ---------- ۴) وردپرس ----------
mkdir -p "$DEMO_DIR"; cd "$DEMO_DIR"
[[ -f wp-load.php ]] || wp core download --locale=fa_IR 2>/dev/null || wp core download
[[ -f wp-config.php ]] || wp config create --dbname=$DB_NAME --dbuser=$DB_USER --dbpass=$DB_PASS --dbhost=localhost --dbcharset=utf8mb4

# میو-پلاگین: سایت روی هر دامنه/پورتی درست کار کند (پیش‌نمایش، localhost و...)
mkdir -p wp-content/mu-plugins
cat > wp-content/mu-plugins/zz-dynamic-host.php <<'EOF'
<?php
if ( defined( 'WP_INSTALLING' ) && WP_INSTALLING ) { return; }
/* تشخیص https پشت reverse-proxy (الزام وردپرس ۷ به بالا) */
if ( ! empty( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === strtolower( (string) $_SERVER['HTTP_X_FORWARDED_PROTO'] ) ) {
	$_SERVER['HTTPS'] = 'on';
}
if ( ! defined( 'WP_HOME' ) && isset( $_SERVER['HTTP_HOST'] ) && $_SERVER['HTTP_HOST'] ) {
	$scheme = ( ! empty( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ) ? 'https' : 'http';
	$host   = preg_replace( '/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST'] );
	define( 'WP_HOME', $scheme . '://' . $host );
	define( 'WP_SITEURL', $scheme . '://' . $host );
	remove_action( 'template_redirect', 'redirect_canonical' );
}
EOF

wp core is-installed 2>/dev/null || wp core install \
  --url="http://localhost:$PORT" --title="بازار — فروشگاه اینترنتی" \
  --admin_user=admin --admin_password="Bazaar#2026!shop" \
  --admin_email=admin@bazaar-shop.ir --skip-email

# ---------- ۵) زبان فارسی + ووکامرس ----------
wp core language install fa_IR --activate 2>/dev/null || true
wp option update WPLANG fa_IR 2>/dev/null || true

if [[ ! -d wp-content/plugins/woocommerce ]]; then
  wp plugin install woocommerce --activate 2>/dev/null || {
    curl -sfL -o /tmp/woocommerce.zip https://downloads.wordpress.org/plugin/woocommerce.zip
    unzip -q -o /tmp/woocommerce.zip -d wp-content/plugins/ && rm -f /tmp/woocommerce.zip
    wp plugin activate woocommerce
  }
fi

mkdir -p wp-content/languages/plugins
[[ -f wp-content/languages/plugins/woocommerce-fa_IR.mo ]] || {
  for v in 11.1.0 10.8.0 9.9.5; do
    curl -sfL -o /tmp/wc-fa.zip "https://downloads.wordpress.org/translation/plugin/woocommerce/$v/fa_IR.zip" && break
  done
  unzip -q -o /tmp/wc-fa.zip -d wp-content/languages/plugins/ 2>/dev/null && rm -f /tmp/wc-fa.zip
}
mkdir -p wp-content/languages
[[ -f wp-content/languages/fa_IR.mo ]] || {
  curl -sfL -o /tmp/core-fa.zip "https://downloads.wordpress.org/translation/core/$(wp core version)/fa_IR.zip" && \
  unzip -q -o /tmp/core-fa.zip -d wp-content/languages/ && rm -f /tmp/core-fa.zip
}

# ---------- ۶) قالب (symlink از ریپو) ----------
mkdir -p wp-content/themes
[[ -e wp-content/themes/bazaar ]] || ln -s "$REPO_DIR/wp-content/themes/bazaar" wp-content/themes/bazaar
wp theme activate bazaar

# ---------- ۷) تنظیمات + محتوای نمونه ----------
wp eval '
if ( class_exists( "WC_Install" ) ) { WC_Install::create_pages(); }
update_option( "woocommerce_currency", "IRT" );
update_option( "woocommerce_currency_pos", "right_space" );
update_option( "woocommerce_price_thousand_sep", "," );
update_option( "woocommerce_price_num_decimals", 0 );
update_option( "woocommerce_default_country", "IR:07" );
update_option( "woocommerce_enable_guest_checkout", "yes" );
update_option( "woocommerce_enable_myaccount_registration", "yes" );
update_option( "woocommerce_manage_stock", "yes" );
update_option( "woocommerce_task_list_hidden", "yes" );
update_option( "woocommerce_show_marketplace_suggestions", "no" );
'
wp option update permalink_structure '/%postname%/'
wp rewrite flush --hard

if [[ $WITH_DEMO -eq 1 ]]; then
  wp eval-file "$REPO_DIR/setup/demo-content.php"
fi

# ---------- ۸) سرور ----------
cat > router.php <<'EOF'
<?php
if ( PHP_SAPI === 'cli-server' ) {
	$path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
	$file = __DIR__ . $path;
	if ( '/' !== $path && ( is_file( $file ) || is_dir( $file ) ) ) { return false; }
}
require __DIR__ . '/index.php';
EOF

echo
echo "🎉 دمو آماده است:  http://localhost:$PORT"
echo "   پیشخوان:        http://localhost:$PORT/wp-admin   (admin / Bazaar#2026!shop)"
echo "   (Ctrl+C برای توقف)"
export PHP_CLI_SERVER_WORKERS=8
php -S 0.0.0.0:$PORT router.php
