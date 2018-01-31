<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests;

use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoSymlink;
use PHPUnit\Framework\TestCase;

class CapistranoSymlinkTest extends TestCase
{

    public function testGettersAndSetters()
    {
        $symlink = new CapistranoSymlink();
        $symlink->setName('name');
        $this->assertEquals('name', $symlink->getName());
    }
}
