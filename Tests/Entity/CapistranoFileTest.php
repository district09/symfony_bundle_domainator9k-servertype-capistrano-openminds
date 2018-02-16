<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests;

use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoFile;
use PHPUnit\Framework\TestCase;

class CapistranoFileTest extends TestCase
{

    public function testGettersAndSetters()
    {
        $originalFile = new CapistranoFile();

        $file = new CapistranoFile();
        $file->setName('name');
        $file->setOriginalCapistranoFile($originalFile);

        $this->assertEquals($originalFile, $file->getOriginalCapistranoFile());
        $this->assertEquals('name', $file->getName());
    }

}