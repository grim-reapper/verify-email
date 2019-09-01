##Verify Email 
This is a simple package that verify email if it is exist or not, This is useful for those who want to get real Email address from users. Get real email address while registering users, for app or newsletter. You can also check email is exist or not before sending email to users. It prevents from black listing your server, or hard bouncing .

* Save you from hard bouncing
* Most of the companies buy SMTP email subscritpion, they have limited credits, you can use this simple package to save your email credits

### 1. Requirements
* Laravel 5.5.x to 6.0.x
* PHP >= 7.0

### 2. Installation
1. Require the package using composer 
    ~~~
    composer require imranali/verify-email
    ~~~
2. Add the service provider to the <code>providers</code> in <code>config/app.php</code>

    ~~~
    ImranAli\VerifyEmail\VerifyEmailServiceProvider::class
    ~~~
    and  add <code>Facade</code> as alias in <code>app/config.php</code>
    ~~~
    'Verifyemail' => ImranAli\VerifyEmail\Facades\verifyEmailFacade::class,
    ~~~
    > Laravel 5.5 >= uses package auto discovery, so you do not need to register service provider and Facade manually
3. Publish the configuration
    ~~~
    php artisan vendor:publish --provider="ImranAli\VerifyEmail\VerifyEmailServiceProvider"
    ~~~
### 3. Usage
You have to call <code>checkEmail()</code> method, it takes one argument which is email address that needs to be validate
~~~php
Verifyemaill::checkEmail('test@example.com');
~~~
It returns <code>true</code> if email exist otherwise <code>false</code>

This package comes with few configuration, You can change settings accordingly, configuration contains one field <code>from_email</code> make sure to set valid email address, because this email address will be used to sent request from.
<br><br>
You can also set email at runtime for example using <code>Facade</code> 
~~~
Verifyemail::setEmailFrom('support@domain.com');
~~~

It also has one helper method that can be used to validate email address pattern
~~~
Verifyemail::validate('example@domain.com')
~~~

Here is a complete demo code
~~~php
$email = 'exampl@domain.com';

// can also be set email at runtime (optional)
Verifyemail::setEmailFrom($email);

if (Verifyemail::checkEmail($email)) {
    echo 'email <' . $email . '> exist!';
} elseif (Verifyemail::validate($email)) {
    echo 'email <' . $email . '> valid, but not exist!';
} else {
    echo 'email <' . $email . '> not valid and not exist!';
}
~~~