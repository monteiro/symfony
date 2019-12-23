<?php

declare(strict_types=1);

namespace Symfony\Component\Serializer\Tests\NameConverter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\NameConverter\XmlAttributesConverter;

class XmlAttributesConverterTest extends TestCase
{
    /**
     * @dataProvider normalizeDataProvider
     */
    public function testNormalize(
        ?string $attributePrefix,
        ?string $nodeValueAttributeName,
        string $propertyName,
        string $expectedPropertyName
    ) : void {
        $xmlAttributeConverter = $this->createXmlAttributesConverter($attributePrefix, $nodeValueAttributeName);
        $result = $xmlAttributeConverter->normalize($propertyName);
        $this->assertEquals($expectedPropertyName, $result);
    }

    /**
     * @dataProvider denormalizeDataProvider
     */
    public function testDenormalize(
        ?string $attributePrefix,
        ?string $nodeValueAttributeName,
        string $propertyName,
        string $expectedPropertyName)
    : void {
        $xmlAttributeConverter = $this->createXmlAttributesConverter($attributePrefix, $nodeValueAttributeName);
        $result = $xmlAttributeConverter->denormalize($propertyName);
        $this->assertEquals($expectedPropertyName, $result);
    }

    public function normalizeDataProvider()
    {
        return [
            'defaults to attr extra attribute' => [null, null, 'attrOwnerID', '@OwnerID'],
            'no extra attributes' => [null, null, 'someOtherParam', 'someOtherParam'],
            'node value' => [null, null, 'value', '#'],
            'custom extra attribute prefix' => ['someAttributePrefix', 'nodeValue', 'someAttributePrefixOwnerID', '@OwnerID'],
            'custom node value attribute' => ['someAttributePrefix', 'nodeValue', 'nodeValue', '#']
        ];
    }

    public function denormalizeDataProvider()
    {
        return [
            'defaults to attr extra attribute' => [null, null, '@OwnerID', 'attrOwnerID'],
            'no extra attributes' => [null, null, 'SomeOtherParam', 'SomeOtherParam'],
            'no extra attributes lowercase' => [null, null, 'someOtherParam', 'someOtherParam'],
            'node value' => [null, null, '#', 'value'],
            'custom extra attribute prefix' => ['someAttributePrefix', 'nodeValue', '@OwnerID', 'someAttributePrefixOwnerID'],
            'custom node value attribute' => ['someAttributePrefix', 'nodeValue', '#', 'nodeValue']
        ];
    }

    /**
     * @param string $attributePrefix
     * @param string $nodeValueAttributeName
     * @return XmlAttributesConverter
     */
    private function createXmlAttributesConverter(
        ?string $attributePrefix,
        ?string $nodeValueAttributeName)
    : XmlAttributesConverter {
        if ($attributePrefix === null && $nodeValueAttributeName === null) {
            $xmlAttributeConverter = new XmlAttributesConverter();
        } else {
            $xmlAttributeConverter = new XmlAttributesConverter($attributePrefix, $nodeValueAttributeName);
        }
        return $xmlAttributeConverter;
    }
}
