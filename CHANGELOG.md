LimeLightPHPSDK
===============

v 2.0
1. The first thing that changed was the name, from LimeLightCRMAPI to LimeLightPHPSDK.
2. Created a "Version 1.0" branch to leave the original working code as-is.
3. Split the classes into separate files
4. Eliminated the need for a database
5. Eliminated static usage (allowing for multiple instances of the API)
6. Altered the class constructors; all classes are instantiated by passing the following allowing for finer control with minimal re-writes:
   `$api_username, $api_password, $log_directory = '', $log_file = '', $log_delimiter = '|'`
7. Altered Logger.php, eliminating static usage and allowing for multiple instances of the Logger class
8. Provided documentation via phpDocumentor 2