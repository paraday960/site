#!/usr/bin/env bash
# ============================================================
#  نصب فروشگاه «بازار» روی هاست/VPS با WP-CLI
#  سازگار با: Debian/Ubuntu (نیاز به: php-cli, mysql/mariadb, wp-cli, curl, unzip)
#
#  نمونه اجرا:
#  bash setup/install.sh \
#    --url="https://example.com" --title="فروشگاه من" \
#    --admin-user=admin --admin-pass='StrongPass123!' --admin-email=me@example.com \
#    --db-name=shop --db-user=shop --db-pass='dbpass' --path=/var/www/html \
#    --with-demo
# ============================================================
set -euo pipefail

# ---------- پیش‌فرض‌ها ----------
URL="http://localhost:8080"
TITLE="فروشگاه من"
ADMIN_USER="admin"
ADMIN_PASS=""
ADMIN_EMAIL="admin@example.com"
DB_NAME="wp_shop"
DB_USER="wp_user"
DB_PASS="wp_pass_123"
DB_HOST="localhost"
WP_PATH="."
WITH_DEMO=0

# ---------- خواندن آرگومان‌ها ----------
while [[ $# -gt 0 ]]; do
  case "$1" in
    --url)         URL="$2"; shift 2 ;;
    --title)       TITLE="$2"; shift 2 ;;
    --admin-user)  ADMIN_USER="$2"; shift 2 ;;
    --admin-pass)  ADMIN_PASS="$2"; shift 2 ;;
    --admin-email) ADMIN_EMAIL="$2"; shift 2 ;;
    --db-name)     DB_NAME="$2"; shift 2 ;;
    --db-user)     DB_USER="$2"; shift 2 ;;
    --db-pass)     DB_PASS="$2"; shift 2 ;;
    --db-host)     DB_HOST="$2"; shift 2 ;;
    --path)        WP_PATH="$2"; shift 2 ;;
    --with-demo)   WITH_DEMO=1; shift ;;
    *) echo "آرگومان ناشناخته: $1"; exit 1 ;;
  esac
done

[[ -z "$ADMIN_PASS" ]] && { echo "❌ --admin-pass الزامی است"; exit 1; }

REPO_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
THEME_SRC="$REPO_DIR/wp-content/themes/bazaar"

echo "🛒  نصب فروشگاه بازار"
echo "    مسیر: $WP_PATH | آدرس: $URL"

command -v wp >/dev/null || { echo "❌ WP-CLI نصب نیست. راهنما: wp-cli.org"; exit 1; }

cd "$WP_PATH"
WP="wp --path=$WP_PATH"

# ---------- ۱) دانلود وردپرس (ترجیحاً فارسی) ----------
if [[ ! -f wp-load.php ]]; then
  echo "⬇️  دانلود وردپرس..."
  $WP core download --locale=fa_IR 2>/dev/null || $WP core download
fi

# ---------- ۲) پیکربندی و نصب ----------
if [[ ! -f wp-config.php ]]; then
  $WP config create --dbname="$DB_NAME" --dbuser="$DB_USER" --dbpass="$DB_PASS" --dbhost="$DB_HOST" --dbcharset=utf8mb4
fi
if [[ -z "$($WP core is-installed 2>/dev/null && echo ok)" ]]; then
  $WP core install --url="$URL" --title="$TITLE" \
    --admin_user="$ADMIN_USER" --admin_password="$ADMIN_PASS" --admin_email="$ADMIN_EMAIL" --skip-email
fi
echo "✅ وردپرس نصب شد"

# ---------- ۳) بسته زبان فارسی (در صورت نیاز) ----------
if [[ "$($WP eval 'echo get_locale();')" != "fa_IR" ]]; then
  $WP core language install fa_IR --activate 2>/dev/null || true
  $WP option update WPLANG fa_IR 2>/dev/null || true
fi

# ---------- ۴) ووکامرس ----------
if ! $WP plugin is-installed woocommerce 2>/dev/null; then
  echo "⬇️  نصب ووکامرس..."
  $WP plugin install woocommerce --activate 2>/dev/null || {
    curl -sfL -o /tmp/woocommerce.zip "https://downloads.wordpress.org/plugin/woocommerce.zip"
    unzip -q -o /tmp/woocommerce.zip -d wp-content/plugins/
    rm -f /tmp/woocommerce.zip
    $WP plugin activate woocommerce
  }
fi
$WP plugin activate woocommerce
echo "✅ ووکامرس فعال شد"

# بسته زبان فارسی ووکامرس (دانلود مستقیم در صورت مسدود بودن API)
if [[ ! -f wp-content/languages/plugins/woocommerce-fa_IR.mo ]]; then
  WC_VER=$($WP plugin list --name=woocommerce --field=version | cut -d. -f1,2).0
  for v in "$WC_VER" 11.1.0 10.8.0; do
    if curl -sfL -o /tmp/wc-fa.zip "https://downloads.wordpress.org/translation/plugin/woocommerce/$v/fa_IR.zip"; then
      mkdir -p wp-content/languages/plugins
      unzip -q -o /tmp/wc-fa.zip -d wp-content/languages/plugins/
      rm -f /tmp/wc-fa.zip
      break
    fi
  done
fi

# ---------- ۵) قالب بازار ----------
if [[ -d "$THEME_SRC" && "$WP_PATH" != "$REPO_DIR" ]]; then
  mkdir -p wp-content/themes
  rm -rf wp-content/themes/bazaar
  cp -r "$THEME_SRC" wp-content/themes/bazaar
fi
$WP theme activate bazaar
echo "✅ قالب بازار فعال شد"

# ---------- ۶) تنظیمات ووکامرس ----------
$WP eval '
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
echo "تنظیمات فروشگاه ✓\n";
'
$WP option update permalink_structure '/%postname%/'
$WP rewrite flush --hard

# ---------- ۷) محتوای نمونه ----------
if [[ $WITH_DEMO -eq 1 ]]; then
  $WP eval-file "$REPO_DIR/setup/demo-content.php"
fi

echo
echo "🎉 تمام شد!"
echo "   فروشگاه : $URL"
echo "   پیشخوان : $URL/wp-admin  (کاربر: $ADMIN_USER)"
