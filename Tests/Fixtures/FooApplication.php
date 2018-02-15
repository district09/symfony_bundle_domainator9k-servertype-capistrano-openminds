<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\Fixtures;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AbstractApplication;

class FooApplication extends AbstractApplication
{

    public static function getApplicationType(): string
    {
        return 'foo';
    }

    public static function getFormType(): string
    {
        return 'random';
    }
}