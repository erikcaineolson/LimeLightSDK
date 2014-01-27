LimeLightPHPSDK
===============

SDK for PHP integration with True Marketing Partner's Lime Light API

***UNOFFICIAL***

The Lime Light CRM help information is freely available at http://help.limelightcrm.com

This is still a beta...just a slightly-further-along one. I'm in the process of testing as I put it up. There are companies that will integrate for you, but if you're familiar with PHP you can implement this pretty easily.

Simply include the autoload.php file and instantiate the desired class:
`require_once('autoload.php');

$limelight_membership = new Membership($api_username, $api_password, $log_directory, $log_file, $log_delimiter);`
