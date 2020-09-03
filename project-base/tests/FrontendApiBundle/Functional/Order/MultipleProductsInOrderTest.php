<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\ProductDataFixture;

class MultipleProductsInOrderTest extends AbstractOrderTestCase
{
    public function testCreateFullOrder(): void
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $expected = [
            'data' => [
                'CreateOrder' => [
                    'transport' => [
                        'name' => t('Czech post', [], 'dataFixtures', $firstDomainLocale),
                    ],
                    'payment' => [
                        'name' => t('Cash on delivery', [], 'dataFixtures', $firstDomainLocale),
                    ],
                    'status' => t('New [adjective]', [], 'dataFixtures', $firstDomainLocale),
                    'totalPrice' => [
                        'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('124111.00'),
                        'priceWithoutVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('102579.75'),
                        'vatAmount' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('21531.25'),
                    ],
                    'items' => $this->getExpectedOrderItems(),
                    'firstName' => 'firstName',
                    'lastName' => 'lastName',
                    'email' => 'user@example.com',
                    'telephone' => '+53 123456789',
                    'companyName' => 'Airlocks s.r.o.',
                    'companyNumber' => '1234',
                    'companyTaxNumber' => 'EU4321',
                    'street' => '123 Fake Street',
                    'city' => 'Springfield',
                    'postcode' => '12345',
                    'country' => 'CZ',
                    'differentDeliveryAddress' => true,
                    'deliveryFirstName' => 'deliveryFirstName',
                    'deliveryLastName' => 'deliveryLastName',
                    'deliveryCompanyName' => null,
                    'deliveryTelephone' => null,
                    'deliveryStreet' => 'deliveryStreet',
                    'deliveryCity' => 'deliveryCity',
                    'deliveryPostcode' => '13453',
                    'deliveryCountry' => 'SK',
                    'note' => 'Thank You',
                ],
            ],
        ];

        $orderMutation = $this->getOrderMutation(__DIR__ . '/Resources/multipleProductsInOrder.graphql');

        $this->assertQueryWithExpectedArray($orderMutation, $expected);
    }

    /**
     * @param string $filePath
     * @return string
     */
    protected function getOrderMutation(string $filePath): string
    {
        $mutation = parent::getOrderMutation($filePath);

        $replaces = [
            '___UUID_PRODUCT_2___' => $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '72')->getUuid(),
            '___UUID_PRODUCT_3___' => $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '80')->getUuid(),
            '___UUID_PRODUCT_4___' => $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '81')->getUuid(),
            '___UUID_PRODUCT_5___' => $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '77')->getUuid(),
            '___UUID_PRODUCT_6___' => $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '2')->getUuid(),
        ];

        return strtr($mutation, $replaces);
    }

    /**
     * @return array
     */
    protected function getExpectedOrderItems(): array
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        return [
            0 => [
                'name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], 'dataFixtures', $firstDomainLocale),
                'unitPrice' => [
                    'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('3499.00'),
                    'priceWithoutVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('2891.75'),
                    'vatAmount' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('607.25'),
                ],
                'totalPrice' => [
                    'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('34990.00'),
                    'priceWithoutVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('28917.25'),
                    'vatAmount' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('6072.75'),
                ],
                'quantity' => 10,
                'vatRate' => '21.0000',
                'unit' => t('pcs', [], 'dataFixtures', $firstDomainLocale),
            ],
            1 => [
                'name' => t('100 Czech crowns ticket', [], 'dataFixtures', $firstDomainLocale),
                'unitPrice' => [
                    'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('121.00'),
                    'priceWithoutVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('100.00'),
                    'vatAmount' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('21.00'),
                ],
                'totalPrice' => [
                    'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('12100.00'),
                    'priceWithoutVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('10000.00'),
                    'vatAmount' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('2100.00'),
                ],
                'quantity' => 100,
                'vatRate' => '21.0000',
                'unit' => t('pcs', [], 'dataFixtures', $firstDomainLocale),
            ],
            2 => [
                'name' => t('27” Hyundai T27D590EY', [], 'dataFixtures', $firstDomainLocale),
                'unitPrice' => [
                    'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('7500.75'),
                    'priceWithoutVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('6199.00'),
                    'vatAmount' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('1301.75'),
                ],
                'totalPrice' => [
                    'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('7500.75'),
                    'priceWithoutVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('6199.00'),
                    'vatAmount' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('1301.75'),
                ],
                'quantity' => 1,
                'vatRate' => '21.0000',
                'unit' => t('pcs', [], 'dataFixtures', $firstDomainLocale),
            ],
            3 => [
                'name' => t('27” Hyundai T27D590EZ', [], 'dataFixtures', $firstDomainLocale),
                'unitPrice' => [
                    'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('7742.75'),
                    'priceWithoutVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('6399.00'),
                    'vatAmount' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('1343.75'),
                ],
                'totalPrice' => [
                    'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('15485.50'),
                    'priceWithoutVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('12798.00'),
                    'vatAmount' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('2687.50'),
                ],
                'quantity' => 2,
                'vatRate' => '21.0000',
                'unit' => t('pcs', [], 'dataFixtures', $firstDomainLocale),
            ],
            4 => [
                'name' => t('30” Hyundai 22MT44D', [], 'dataFixtures', $firstDomainLocale),
                'unitPrice' => [
                    'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('4838.75'),
                    'priceWithoutVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('3999.00'),
                    'vatAmount' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('839.75'),
                ],
                'totalPrice' => [
                    'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('24193.75'),
                    'priceWithoutVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('19994.75'),
                    'vatAmount' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('4199.00'),
                ],
                'quantity' => 5,
                'vatRate' => '21.0000',
                'unit' => t('pcs', [], 'dataFixtures', $firstDomainLocale),
            ],
            5 => [
                'name' => t('32" Philips 32PFL4308', [], 'dataFixtures', $firstDomainLocale),
                'unitPrice' => [
                    'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('9890.00'),
                    'priceWithoutVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('8173.50'),
                    'vatAmount' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('1716.50'),
                ],
                'totalPrice' => [
                    'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('29670.00'),
                    'priceWithoutVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('24520.75'),
                    'vatAmount' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('5149.25'),
                ],
                'quantity' => 3,
                'vatRate' => '21.0000',
                'unit' => t('pcs', [], 'dataFixtures', $firstDomainLocale),
            ],
            6 => [
                'name' => t('Cash on delivery', [], 'dataFixtures', $firstDomainLocale),
                'unitPrice' => [
                    'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('50.00'),
                    'priceWithoutVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('50.00'),
                    'vatAmount' => '0.00',
                ],
                'totalPrice' => [
                    'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('50.00'),
                    'priceWithoutVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('50.00'),
                    'vatAmount' => '0.00',
                ],
                'quantity' => 1,
                'vatRate' => '0.0000',
                'unit' => null,
            ],
            7 => [
                'name' => t('Czech post', [], 'dataFixtures', $firstDomainLocale),
                'unitPrice' => [
                    'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('121.00'),
                    'priceWithoutVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('100.00'),
                    'vatAmount' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('21.00'),
                ],
                'totalPrice' => [
                    'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('121.00'),
                    'priceWithoutVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('100.00'),
                    'vatAmount' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('21.00'),
                ],
                'quantity' => 1,
                'vatRate' => '21.0000',
                'unit' => null,
            ],
        ];
    }
}
