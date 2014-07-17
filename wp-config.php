<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wpbasic');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '%pgfV/</Ldtyq8?+-_=<Z8tN_?B>X-*b!ItR<+M}pHbdnXSy|o{{q9&KUOlF)eJV');
define('SECURE_AUTH_KEY',  'bCI|UshK2b0)_Y3BArz1~YQZoE}-84VXDu`:{Pmr8QXvzh|h~SSi`%X; WZ]}daW');
define('LOGGED_IN_KEY',    'kX>!Y)!h4G~KIqsiLWK>i)Fc~*+^nipKcG->B]l^;sJ3<gO@]~PIrNJGF9]uKp{>');
define('NONCE_KEY',        'L4-G*IQubdJ?S0KIqlN!7N!~#W,|pup-l<?{E6-O2.nzTy]/ j@Y_EbiBH5o,.T_');
define('AUTH_SALT',        'YZZInhQy)`fOG3|7</yt~jDM+s1YbJM|yu3}gpz|2ga/@x`!{uh*Z}w+N[8$IAw`');
define('SECURE_AUTH_SALT', ':%`b;V(i9N8-a@w^>2y>@w-T0Fz_AFC4K[Sr8,TpGEy8Ov&a-b#bB*KJtpt;xEco');
define('LOGGED_IN_SALT',   'X>nUf^]@jw!HNA[KxMBz(*c87(j.8PgnFFK>CI7Bi-peU?931u+K/zd/ormLwB4t');
define('NONCE_SALT',       ']:G}M(T<~?VMM]+EaC{HGN/9{LZ+Z/:dvVm!+T@Z4,-mp5Y>pdzh>jx<rVmkso9p');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
