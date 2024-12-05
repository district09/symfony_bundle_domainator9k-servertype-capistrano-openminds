<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests;

use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoFolder;
use PHPUnit\Framework\TestCase;

class CapistranoFolderTest extends TestCase
{

    public function testGettersAndSetters()
    {
        $folder = new CapistranoFolder();
        $folder->setName('name');
        $this->assertEquals('name', $folder->getName());
    }
}
