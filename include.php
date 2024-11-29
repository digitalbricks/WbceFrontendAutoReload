<?php

// Stop direct file access
if(count(get_included_files()) ==1){header('Location: ../index.php');die();}

if (!function_exists('frontendautoreload')) {
    include_once("classes/frontendautoreload.php");
    function frontendautoreload() {
        return new FrontendAutoReload();
    }
}




