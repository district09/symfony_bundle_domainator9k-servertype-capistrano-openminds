<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provider;


use DigipolisGent\SettingBundle\Provider\DataTypeProviderInterface;

/**
 * Class DataTypeProvider
 * @package DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provider
 */
class DataTypeProvider implements DataTypeProviderInterface
{
    public function getDataTypes()
    {
        return [
            [
                'key' => 'capistrano_file',
                'label' => 'Capistrano file',
                'required' => true,
                'field_type' => 'capistrano_file',
                'entity_types' => ['application_type_environment','application_environment'],
            ],
        ];
    }
}