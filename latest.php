<?php

// load config file to access WB constants
require_once('../../config.php');
require_once 'classes/ExcludeFilter.php';
require_once 'classes/FrontendAutoReload.php';
$far = new frontendautoreload();
echo $far->returnResults();