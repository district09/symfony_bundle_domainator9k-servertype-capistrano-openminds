<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\Provider;

use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provider\DataTypeProvider;
use PHPUnit\Framework\TestCase;

class DataTypeProviderTest extends TestCase
{

    public function testGetDataTypes()
    {
        $keys = [
            'key' => 'string',
            'label' => 'string',
            'required' => 'boolean',
            'field_type' => 'string',
            'entity_types' => 'array',
        ];

        $dataTypeProvider = new DataTypeProvider();
        foreach ($dataTypeProvider->getDataTypes() as $dataType) {
            foreach ($keys as $name => $type){
                $this->assertArrayHasKey($name, $dataType);
                $this->assertInternalType($type,$dataType[$name]);
            }
        }
    }
}