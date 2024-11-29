<?php

/**
 * TODO:
 * Get template name from post as $page['template'] is not available here (see __construct)
 */




// load config file to access WB constants
require_once('../../config.php');

require_once 'classes/frontendautoreload.php';
$far = new frontendautoreload();
echo $far->returnResults();