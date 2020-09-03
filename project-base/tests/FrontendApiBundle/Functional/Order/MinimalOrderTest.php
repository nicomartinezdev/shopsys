<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

class MinimalOrderTest extends AbstractOrderTestCase
{
    public function testCreateMinimalOrderMutation(): void
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
                        'priceWithoutVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('29067.25'),
                        'vatAmount' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('6093.75'),
                    ],
                    'items' => $this->getExpectedOrderItems(),
                    'firstName' => 'firstName',
                    'lastName' => 'lastName',
                    'email' => 'user@example.com',
                    'telephone' => '+53 123456789',
                    'companyName' => null,
                    'companyNumber' => null,
                    'companyTaxNumber' => null,
                    'street' => '123 Fake Street',
                    'city' => 'Springfield',
                    'postcode' => '12345',
                    'country' => 'CZ',
                    'differentDeliveryAddress' => false,
                    'deliveryFirstName' => 'firstName',
                    'deliveryLastName' => 'lastName',
                    'deliveryCompanyName' => null,
                    'deliveryTelephone' => '+53 123456789',
                    'deliveryStreet' => '123 Fake Street',
                    'deliveryCity' => 'Springfield',
                    'deliveryPostcode' => '12345',
                    'deliveryCountry' => 'CZ',
                    'note' => null,
                ],
            ],
        ];

        $orderMutation = $this->getOrderMutation(__DIR__ . '/Resources/minimalOrder.graphql');

        $this->assertQueryWithExpectedArray($orderMutation, $expected);
    }
}
