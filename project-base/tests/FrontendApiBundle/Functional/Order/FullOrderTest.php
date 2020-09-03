<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

class FullOrderTest extends AbstractOrderTestCase
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
                        'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('35161.00'),
                        'priceWithoutVat' => $this->getPriceWithoutVatConvertedToDomainDefaultCurrency('29067.25'),
                        'vatAmount' => $this->getPriceWithoutVatConvertedToDomainDefaultCurrency('6093.75'),
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

        $orderMutation = $this->getOrderMutation(__DIR__ . '/Resources/fullOrder.graphql');

        $this->assertQueryWithExpectedArray($orderMutation, $expected);
    }
}
