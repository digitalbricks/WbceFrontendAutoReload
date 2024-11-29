<?php
// load config file to access WB constants
require_once('../../config.php');

require_once 'classes/frontendautoreload.php';
$far = new frontendautoreload();
echo $far->returnResults();