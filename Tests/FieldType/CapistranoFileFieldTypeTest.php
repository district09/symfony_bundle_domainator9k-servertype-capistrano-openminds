<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\FieldType;

use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoFolder;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\FieldType\CapistranoFolderFieldType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class CapistranoFolderFieldTypeTest extends AbstractFieldTypeTest
{

    public function testGetName()
    {
        $this->assertEquals('capistrano_folder', CapistranoFolderFieldType::getName());
    }

    public function testGetFormType()
    {
        $entityManager = $this->getEntityManagerMock();
        $fieldType = new CapistranoFolderFieldType($entityManager);
        $this->assertEquals(CollectionType::class, $fieldType->getFormType());
    }

    public function testEncodeValue()
    {
        $this->generalTestEncodeValue(
            CapistranoFolderFieldType::class,
            CapistranoFolder::class
        );
    }

    public function testDecodeValue()
    {
        $this->generalTestDecodeValue(
            CapistranoFolderFieldType::class,
            CapistranoFolder::class
        );
    }
}
