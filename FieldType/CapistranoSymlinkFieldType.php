<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\FieldType;

use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoSymlink;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Form\Type\CapistranoSymlinkFormType;

/**
 * Class CapistranoSymlinkFieldType
 * @package DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\FieldType
 */
class CapistranoSymlinkFieldType extends AbstractCapistranoFieldType
{

    /**
     * @param $value
     * @return array
     */
    public function getOptions($value): array
    {
        return $this->getCapistranoOptions(
            CapistranoSymlinkFormType::class,
            CapistranoSymlink::class,
            'capistrano_symlink',
            $value
        );
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'capistrano_symlink';
    }

    /**
     * @param $value
     * @return string
     */
    public function encodeValue($value): ?string
    {
        return $this->encodeCapistranoValue($value);
    }

    /**
     * @param $value
     * @return array
     */
    public function decodeValue($value)
    {
        return $this->decodeCapistranoValue(CapistranoSymlink::class, $value);
    }
}
