<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Redis settings
| -------------------------------------------------------------------------
| Your Redis servers can be specified below.
|
|	See: https://www.codeigniter.com/user_guide/libraries/caching.html#redis-caching
|	http://redis.io
*/
$config['socket_type'] = 'tcp'; //`tcp` or `unix`
$config['socket'] = '/var/run/redis.sock'; // in case of `unix` socket type
$config['host'] = getenv('REDIS_HOST');
$config['password'] = NULL;
$config['port'] = 6379;
$config['timeout'] = 0;
