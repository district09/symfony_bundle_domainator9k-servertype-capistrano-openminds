<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\FieldType;

use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoSymlink;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\FieldType\CapistranoSymlinkFieldType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class CapistranoSymlinkFieldTypeTest extends AbstractFieldTypeTest
{

    public function testGetName()
    {
        $this->assertEquals('capistrano_symlink', CapistranoSymlinkFieldType::getName());
    }

    public function testGetFormType()
    {
        $entityManager = $this->getEntityManagerMock();
        $fieldType = new CapistranoSymlinkFieldType($entityManager);
        $this->assertEquals(CollectionType::class, $fieldType->getFormType());
    }

    public function testEncodeValue()
    {
        $this->generalTestEncodeValue(
            CapistranoSymlinkFieldType::class,
            CapistranoSymlink::class
        );
    }

    public function testDecodeValue()
    {
        $this->generalTestDecodeValue(
            CapistranoSymlinkFieldType::class,
            CapistranoSymlink::class
        );
    }
}
