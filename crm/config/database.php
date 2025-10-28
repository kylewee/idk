<?php

// define database connection (env-aware for Docker and local)
  define('DB_SERVER', getenv('DB_HOST') ?: 'localhost'); // eg, localhost - should not be empty for productive servers
  define('DB_SERVER_USERNAME', getenv('DB_USER') ?: 'kylewee');
  define('DB_SERVER_PASSWORD', getenv('DB_PASS') ?: 'rainonin');
  define('DB_SERVER_PORT', getenv('DB_PORT') ?: '');		
  define('DB_DATABASE', getenv('DB_NAME') ?: 'rukovoditel');  	  
  