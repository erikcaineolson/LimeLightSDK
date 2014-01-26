<?php
    /*******************************************************\
    |                                                       |
    |   Add the required classes to the autoload stack      |
    \*******************************************************/

    spl_autoload_register(function($class){
        require_once('classes/' . $class . '.php');
    });
?>