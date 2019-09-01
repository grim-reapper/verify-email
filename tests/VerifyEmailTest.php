<?php


namespace ImranAli\VerifyEmail\Tests;


use ImranAli\VerifyEmail\Facades\verifyEmailFacade;
use Tests\TestCase;

class VerifyEmailTest extends TestCase
{
    /** @test */
    public function is_provided_email_valid()
    {
        $email = verifyEmailFacade::validate('test@test.com');
        $this->assertTrue($email);
    }

    /** @test */
    public function to_check_if_email_is_exist()
    {
        verifyEmailFacade::setEmailFrom('imran@wtwm.com');
        $status = verifyEmailFacade::checkEmail('imran.ali125@yahoo.com');
        $this->assertTrue($status);
    }

    /** @test */
    public function to_check_if_email_not_exist()
    {
        verifyEmailFacade::setEmailFrom('imran@gmail.com');
        $status = verifyEmailFacade::checkEmail('test@test.com');
        $this->assertFalse($status);
    }
}