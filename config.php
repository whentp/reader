<?php

// Set default timezone
date_default_timezone_set('UTC');
ini_set("default_socket_timeout", 10);
ini_set('max_execution_time', 3000);

define('HOSTNAME', 'localhost');

//10 minutes. This value is none of the business of cron job. don't expect the program can fetch items without setting up a cron job or manually running cron.fetch.php.
define('MINFETCHINTERVAL', 1000);
