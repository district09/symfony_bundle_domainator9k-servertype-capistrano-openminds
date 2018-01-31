<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\FieldType;

use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoFolder;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Form\Type\CapistranoFolderFormType;

/**
 * Class CapistranoFolderFieldType
 * @package DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\FieldType
 */
class CapistranoFolderFieldType extends AbstractCapistranoFieldType
{

    /**
     * @param $value
     * @return array
     */
    public function getOptions($value): array
    {
        return $this->getCapistranoOptions(
            CapistranoFolderFormType::class,
            CapistranoFolder::class,
            'capistrano_folder',
            $value
        );
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'capistrano_folder';
    }

    /**
     * @param $value
     * @return string
     */
    public function encodeValue($value): string
    {
        return $this->encodeCapistranoValue($value);
    }

    /**
     * @param $value
     * @return array
     */
    public function decodeValue($value)
    {
        return $this->decodeCapistranoValue(CapistranoFolder::class, $value);
    }
}
