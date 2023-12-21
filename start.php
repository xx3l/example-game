<?php

use \Workerman\Worker;

define('GLOBAL_START', true);

// PHP socket.io
require_once __DIR__ . '/start_worker.php';
// web
require_once __DIR__ . '/start_web.php';

Worker::runAll();
