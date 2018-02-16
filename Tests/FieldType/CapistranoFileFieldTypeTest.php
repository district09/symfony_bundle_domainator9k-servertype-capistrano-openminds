<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\FieldType;

use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoFile;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\FieldType\CapistranoFileFieldType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class CapistranoFileFieldTypeTest extends AbstractFieldTypeTest
{

    public function testGetName()
    {
        $this->assertEquals('capistrano_file', CapistranoFileFieldType::getName());
    }

    public function testGetFormType()
    {
        $entityManager = $this->getEntityManagerMock();
        $fieldType = new CapistranoFileFieldType($entityManager);
        $this->assertEquals(CollectionType::class, $fieldType->getFormType());
    }

    public function testEncodeValue()
    {
        $this->generalTestEncodeValue(
            CapistranoFileFieldType::class,
            CapistranoFile::class
        );
    }

    public function testDecodeValue()
    {
        $this->generalTestDecodeValue(
            CapistranoFileFieldType::class,
            CapistranoFile::class
        );
    }

    public function testGetOptionsWithApplicationEnvironment()
    {
        $this->generalTestGetOptionsWithApplicationEnvironment(
            CapistranoFileFieldType::class,
            CapistranoFile::class
        );
    }

    public function testGetOptions()
    {
        $this->generalTestGetOptions(
            CapistranoFileFieldType::class,
            CapistranoFile::class
        );
    }
}
