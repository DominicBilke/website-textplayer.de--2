<?php
/**
 * Database configuration for TextPlayer.de
 * For production: use environment variables or a config file outside web root
 */
if (getenv('TEXTPLAYER_DB_HOST')) {
    define('DB_HOST', getenv('TEXTPLAYER_DB_HOST'));
    define('DB_NAME', getenv('TEXTPLAYER_DB_NAME'));
    define('DB_USER', getenv('TEXTPLAYER_DB_USER'));
    define('DB_PASS', getenv('TEXTPLAYER_DB_PASS'));
} else {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'd03d4320');
    define('DB_USER', 'd03d4320');
    define('DB_PASS', 'ypu55fZrHscwoNmXqXuQ');
}
