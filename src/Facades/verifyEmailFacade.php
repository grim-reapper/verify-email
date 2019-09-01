<?php


namespace ImranAli\VerifyEmail\Facades;


use Illuminate\Support\Facades\Facade;
use ImranAli\VerifyEmail\verifyEmail;

class verifyEmailFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return verifyEmail::class;
    }

}