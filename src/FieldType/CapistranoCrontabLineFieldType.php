<?php

namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\FieldType;

use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoCrontabLine;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Form\Type\CapistranoCrontabLineFormType;

/**
 * Class CapistranoCrontabLineFieldType
 * @package DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\FieldType
 */
class CapistranoCrontabLineFieldType extends AbstractCapistranoFieldType
{
    /**
     * @param $value
     * @return array
     */
    public function getOptions($value): array
    {
        return $this->getCapistranoOptions(
            CapistranoCrontabLineFormType::class,
            CapistranoCrontabLine::class,
            'capistrano_crontab_line',
            $value
        );
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'capistrano_crontab_line';
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
        return $this->decodeCapistranoValue(CapistranoCrontabLine::class, $value);
    }
}
