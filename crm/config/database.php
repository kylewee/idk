<?php
/**
 * DEPRECATED: This file is maintained for backward compatibility only.
 * New code should use the Config class directly.
 *
 * @deprecated Use Config class instead (see /src/Config.php)
 */

// Load the Config class
require_once __DIR__ . '/../../src/Config.php';

// Define database connection from environment variables
define('DB_SERVER', Config::get('DB_SERVER', 'localhost'));
define('DB_SERVER_USERNAME', Config::get('DB_SERVER_USERNAME'));
define('DB_SERVER_PASSWORD', Config::get('DB_SERVER_PASSWORD'));
define('DB_SERVER_PORT', Config::get('DB_SERVER_PORT', ''));
define('DB_DATABASE', Config::get('DB_DATABASE'));  	  
  