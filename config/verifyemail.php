<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SMTP port number
    |--------------------------------------------------------------------------
    |
    | port to used for SMTP connection, default is 25
    | do not change this port
    */

    'smtp_port' => 25,

    /*
    |--------------------------------------------------------------------------
    | Send Email Address
    |--------------------------------------------------------------------------
    |
    | Sender email address that will used to send email from
    | Replace your own email address here
    */

    'from_email' => 'root@localhost',

    /*
    |--------------------------------------------------------------------------
    | Exception Handling
    |--------------------------------------------------------------------------
    |
    | Used to throw exceptions or not
    | set true to print exceptions
    */

    'exceptions' => false,

    /*
    |--------------------------------------------------------------------------
    | Debugging the library
    |--------------------------------------------------------------------------
    |
    | Enable or disable debugging mode
    |
    */

    'debug' => false,

    /*
    |--------------------------------------------------------------------------
    | Debug output format
    |--------------------------------------------------------------------------
    |
    | used to print out debug output, there are three type to output logs,
    | 1. `echo` Output plan-text as is, approprite for cli // this is default
    | 2. `html` Output escaped, line breaks converted to `<br>`, appropriated for browser output
    | 3. `log` Output to error log as configured in php.ini
    */

    'debug_output' => 'echo',
];