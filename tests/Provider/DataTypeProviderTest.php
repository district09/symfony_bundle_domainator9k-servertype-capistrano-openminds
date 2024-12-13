<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\Provider;

use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provider\DataTypeProvider;
use PHPUnit\Framework\TestCase;

class DataTypeProviderTest extends TestCase
{

    public function testGetDataTypes()
    {
        $keys = [
            'key' => 'is_string',
            'label' => 'is_string',
            'required' => 'is_bool',
            'field_type' => 'is_string',
            'entity_types' => 'is_array',
        ];

        $dataTypeProvider = new DataTypeProvider();
        foreach ($dataTypeProvider->getDataTypes() as $dataType) {
            foreach ($keys as $name => $func){
                $this->assertArrayHasKey($name, $dataType);
                $this->assertTrue($func($dataType[$name]), "$func failed for $name");
            }
        }
    }
}
