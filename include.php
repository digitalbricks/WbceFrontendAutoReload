<?php

// Stop direct file access
if(count(get_included_files()) ==1){header('Location: ../index.php');die();}

if (!function_exists('frontendautoreload')) {
    require_once('classes/ExcludeFilter.php');
    include_once("classes/FrontendAutoReload.php");
    function frontendautoreload() {
        return new FrontendAutoReload();
    }
}




