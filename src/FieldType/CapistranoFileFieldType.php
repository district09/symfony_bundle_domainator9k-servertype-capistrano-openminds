<?php

namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\FieldType;

use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoFile;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Form\Type\CapistranoFileFormType;

/**
 * Class CapistranoFileFieldType
 * @package DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\FieldType
 */
class CapistranoFileFieldType extends AbstractCapistranoFieldType
{
    /**
     * @param $value
     * @return array
     */
    public function getOptions($value): array
    {
        return $this->getCapistranoOptions(
            CapistranoFileFormType::class,
            CapistranoFile::class,
            'capistrano_file',
            $value
        );
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'capistrano_file';
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
        return $this->decodeCapistranoValue(CapistranoFile::class, $value);
    }
}
