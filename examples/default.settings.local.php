<?php

/**
 * @file
 * Drupal instance-specific configuration file. This is included at the end of
 * the distributed settings.php, so use this to set everything related to
 * your local instance.
 *
 * Example settings below. See the original settings.php for full
 * documentation.
 *
 * USAGE:
 *   Copy to settings.local.php and edit.
 *   Your local copy will be ignored by Git.
 *
 * Command to check the current settings:
 *   drush eval 'include conf_path() . "/settings.php"; print_r($conf);'
 *
 */

/**
 * Database settings:
 *
 */
$databases['default']['default'] = array(
  'driver' => 'mysql',
  'database' => 'DB_NAME',
  'username' => 'DB_USER',
  'password' => 'DB_PASS',
  'host' => 'DB_HOST',
  'prefix' => '',
);

/**
 * Set environment variables (local, dev, test, prod) - important, do not remove
 */
$conf['environment'] = $env = 'local';

/**
 * Drupal automatically generates a unique session cookie name for each site
 * based on its full domain name. If you have multiple domains pointing at the
 * same Drupal site, you can either redirect them all to a single domain (see
 * comment in .htaccess), or uncomment the line below and specify their shared
 * base domain. Doing so assures that users remain logged in as they cross
 * between your various domains. Make sure to always start the $cookie_domain
 * with a leading dot, as per RFC 2109.
 */
switch ($env) {
  case 'prod':
    $cookie_domain = @$_SERVER["SERVER_NAME"];
    break;
    break;
  default:
    $cookie_domain = @$_SERVER["SERVER_NAME"];
}

/**
 * Sagepay Server settings
 *
 */
switch ($env) {
  case 'prod':
    $conf['sagepay_txn_mode'] = 'prod';
    $conf['sagepay_bypass_commerce_card_validation'] = 0;
    $conf['sagepage_use_test_data'] = 0;
    break;
  default:
    $conf['sagepay_txn_mode'] = 'test';
    $conf['sagepay_bypass_commerce_card_validation'] = 1;
    break;
}

/**
 * E-mail settings.
 * This will intercepts all outgoing emails from a Drupal site
 * and reroutes them to a predefined configurable email address.
 * Requirements: reroute_email module to be enabled
 */
switch ($env) {
  case 'prod':
    $conf['reroute_email_enable'] = 0;
    break;
  default: // for local/dev/test
    $conf['site_mail'] = 'admin@example.com';
    $conf['reroute_email_enable'] = 1;
    $conf['reroute_email_address'] = 'example@example.com';
    $conf['mail_system'] = array( 'default-system' => 'DevelMailLog' );
}

/**
 * Reverse Proxy Configuration
 */
switch ($env) {
  case 'prod':
    $conf['reverse_proxy'] = TRUE;
    $conf['reverse_proxy_addresses'] = array('127.0.0.1');
    break;
  default: // for local/dev
    $conf['reverse_proxy'] = FALSE;;
}

/**
 * Memcached Configuration
 */
if (class_exists('Memcache') || class_exists('Memcached')) {

  // Make memcache the default cache class.
  $conf['cache_backends'][] = 'sites/all/modules/contrib/memcache/memcache.inc';
  $conf['cache_default_class'] = 'MemCacheDrupal';
  $conf['memcache_key_prefix'] = intval(@$_SERVER['HTTP_HOST'], 36) . '_'; // Use 'base36 to int' based on the hostname as cache key prefix.

  // Ensure that the special cache_form bin is assigned to non-volatile storage.
  $conf['cache_class_cache_form'] = 'DrupalDatabaseCache';

  // Support for Memcached PECL extension
  // This new extension backends to libmemcached
  // and allows you to use some of the newer advanced features in memcached 1.4.
  if (class_exists('Memcached')) {
    // Enable the binary protocol, which is more advanced and faster.
    $conf['memcache_options'] = array(
        Memcached::OPT_BINARY_PROTOCOL => TRUE,
    );

    // Memcached takes options directly from Drupal.
    $conf['memcache_options'] = array(
        Memcached::OPT_COMPRESSION => TRUE, // Turn off compression, as this takes more CPU cycles than its worth for most users.
        Memcached::OPT_DISTRIBUTION => Memcached::DISTRIBUTION_CONSISTENT, // Allows you to add/remove servers easily.
    );
  }
}

/**
 * Varnishd Configuration
 *
 * This cache implementation can be used together with Varnish. You can't really use it to store or get any values, but you can use it to purge your caches.
 * This cache implementation should ONLY be used for cache_page and no other cache bin!
 */
if (class_exists('VarnishCache')) {
  // Add Varnish as the page cache handler.
  $conf['cache_backends'][] = 'sites/all/modules/contrib/varnish/varnish.cache.inc';
  // Drupal 7 does not cache pages when we invoke hooks during bootstrap. This needs to be disabled.
  $conf['page_cache_invoke_hooks'] = FALSE;
  $conf['cache_class_cache_page'] = 'VarnishCache';
  // $conf['varnish_control_key'] = ''; // This is managed from salt://varnishd/secret
}

/**
 * XML Site Map.
 */
switch ($env) {
  case 'prod':
  default:
    // Avoid sending your XML Site Map to live search engines on test environments.
    $conf['xmlsitemap_engines_engines'] = array(); // Do not use Sitemap on dev envs
    $conf['xmlsitemap_rebuild_needed'] = FALSE;    // --;;--
    $conf['xmlsitemap_regenerate_needed'] = FALSE; // --;;--
    break;
}

/**
 * Variables for testing environments.
 */
switch ($env) {
  case 'dev':
  case 'local':
    // Disable cache
    $conf['cache'] = 0;
    $conf['block_cache'] = 0;
    $conf['page_compression'] = 0;
    $conf['preprocess_css'] = 0;
    $conf['preprocess_js'] = 0;
    // Disables warning about HTTP request fails.
    $conf['drupal_http_request_fails'] = FALSE;
}

/**
 * This needs to be set for Secure Pages.
 */
// $conf['https'] = TRUE;


/**
 * Bootstrap variables set only in CLI (drush)
 * See also: drushrc.php (Variable overrides)
 */
if (drupal_is_cli()) {
  $conf['drupal_http_request_fails'] = FALSE;
}

/**
 * General variables
 */
$conf['install_profile'] = 'ads';

/**
 * Set session cookie lifetime (in seconds), i.e. the time from the session is created to the cookie expires, i.e. when the browser is expected to discard
 * the cookie. The value 0 means "until the browser is closed".
 * When using Persistent Login, it should be 0 so that PHP sessions end when the user closes his/her browser.
 */
ini_set('session.cookie_lifetime', 0);

/**
 * Other variables
 */
// $conf['user_failed_login_ip_limit'] = PHP_INT_MAX; // Activate no login limits in case of flood issues.
// $drupal_hash_salt = ''; // Salt for one-time login links and cancel links, form tokens, etc.
// $base_url = getenv('BASE_URL'); // Base URL (NO trailing slash!).
