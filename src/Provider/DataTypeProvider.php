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
                'key' => 'capistrano_private_key_location',
                'label' => 'Capistrano private key location',
                'required' => true,
                'field_type' => 'file_location',
                'entity_types' => ['server'],
            ],
            [
                'key' => 'capistrano_private_key_passphrase',
                'label' => 'Capistrano private key passphrase',
                'required' => false,
                'field_type' => 'string',
                'entity_types' => ['server'],
            ],
            [
                'key' => 'manage_capistrano',
                'label' => 'Manage capistrano',
                'required' => false,
                'field_type' => 'boolean',
                'entity_types' => ['server'],
            ],
            [
                'key' => 'capistrano_file',
                'label' => 'Capistrano file',
                'required' => false,
                'field_type' => 'capistrano_file',
                'entity_types' => ['application_type_environment', 'application_environment'],
            ],
            [
                'key' => 'capistrano_folder',
                'label' => 'Capistrano folder',
                'required' => false,
                'field_type' => 'capistrano_folder',
                'entity_types' => ['application_type_environment', 'application_environment'],
            ],
            [
                'key' => 'capistrano_symlink',
                'label' => 'Capistrano symlink',
                'required' => false,
                'field_type' => 'capistrano_symlink',
                'entity_types' => ['application_type_environment', 'application_environment'],
            ],
            [
                'key' => 'capistrano_crontab_line',
                'label' => 'Capistrano crontab line',
                'required' => false,
                'field_type' => 'capistrano_crontab_line',
                'entity_types' => ['application_type_environment', 'application_environment'],
            ],
        ];
    }
}
