<?php
// To run this example application against a live Windows Azure Table,
// you must supply your account name and key. You can obtain these from
// the Windows Azure portal after you have created a Windows Azure subscription.
define("STORAGE_ACCOUNT_NAME", "your_storage_account_name");
define("STORAGE_ACCOUNT_KEY", "your_storage_account_key");

// If you want to run this example application against a live Windows
// Azure Table, set the value of PROD_SITE to true. Otherwise, set it to false
// to run the application against Development Storage.
// Note: You will have to start both the Compute Emulator and Development 
// Storage before running the application against Development Storage.
define("PROD_SITE", true);


define("EXCEPTION_TABLE", "ExceptionEntry");
define("STATUS_TABLE", "ScaleStatus");

// Define DNS_PREFIX as the DNS prefix for you hosted service.
// e.g. If your hosted service endpoint is myprefix.cloudapp.net,
// then myprefix is your DNS prefix.
define('DNS_PREFIX', 'the dns prefix for your hosted service');

// Define the name of your web role. This is in your ServiceConfiguration.cscfg file.
define('ROLE_NAME', 'your web role name');

// Define DEPLOYMENT_SLOT as production or staging depending on
// which slot you want to scale.
define('DEPLOYMENT_SLOT', 'production or staging');

// Define SUBSCRIPTION_ID as your subscription ID.
define ('SUBSCRIPTION_ID', 'your subscription id');

// Define the interval over which metrics are averaged.
// The default value is 15 minutes.
define ('AVERAGE_INTERVAL', "-15 minutes");

// Define the minimum and maximum number of instances you want running.
define('MIN_INSTANCES', 2); 
define('MAX_INSTANCES', 20);

// Define how often you want metrics collected.
define('COLLECTION_FREQUENCY', 60); // in seconds

$certificate = 'your_cert_name.pem';
?>