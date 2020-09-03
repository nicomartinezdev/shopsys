<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class AbstractOrderTestCase extends GraphQlTestCase
{
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
                    'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('3499'),
                    'priceWithoutVat' => $this->getPriceWithoutVatConvertedToDomainDefaultCurrency('2891.75'),
                    'vatAmount' => $this->getPriceWithoutVatConvertedToDomainDefaultCurrency('607.25'),
                ],
                'totalPrice' => [
                    'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('34990'),
                    'priceWithoutVat' => $this->getPriceWithoutVatConvertedToDomainDefaultCurrency('28917.25'),
                    'vatAmount' => $this->getPriceWithoutVatConvertedToDomainDefaultCurrency('6072.75'),
                ],
                'quantity' => 10,
                'vatRate' => '21.0000',
                'unit' => t('pcs', [], 'dataFixtures', $firstDomainLocale),
            ],
            1 => [
                'name' => t('Cash on delivery', [], 'dataFixtures', $firstDomainLocale),
                'unitPrice' => [
                    'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('50.00'),
                    'priceWithoutVat' => $this->getPriceWithoutVatConvertedToDomainDefaultCurrency('50.00'),
                    'vatAmount' => $this->getPriceWithoutVatConvertedToDomainDefaultCurrency('0.00'),
                ],
                'totalPrice' => [
                    'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('50.00'),
                    'priceWithoutVat' => $this->getPriceWithoutVatConvertedToDomainDefaultCurrency('50.00'),
                    'vatAmount' => $this->getPriceWithoutVatConvertedToDomainDefaultCurrency('0.00'),
                ],
                'quantity' => 1,
                'vatRate' => '0.0000',
                'unit' => null,
            ],
            2 => [
                'name' => t('Czech post', [], 'dataFixtures', $firstDomainLocale),
                'unitPrice' => [
                    'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('121.00'),
                    'priceWithoutVat' => $this->getPriceWithoutVatConvertedToDomainDefaultCurrency('100.00'),
                    'vatAmount' => $this->getPriceWithoutVatConvertedToDomainDefaultCurrency('21.00'),
                ],
                'totalPrice' => [
                    'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('121.00'),
                    'priceWithoutVat' => $this->getPriceWithoutVatConvertedToDomainDefaultCurrency('100.00'),
                    'vatAmount' => $this->getPriceWithoutVatConvertedToDomainDefaultCurrency('21.00'),
                ],
                'quantity' => 1,
                'vatRate' => '21.0000',
                'unit' => null,
            ],
        ];
    }

    /**
     * @param string $filePath
     * @return string
     */
    protected function getOrderMutation(string $filePath): string
    {
        $mutation = file_get_contents($filePath);

        $replaces = [
            '___UUID_PAYMENT___' => $this->getReference(PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY)->getUuid(),
            '___UUID_TRANSPORT___' => $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST)->getUuid(),
            '___UUID_PRODUCT___' => $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1')->getUuid(),
        ];

        return strtr($mutation, $replaces);
    }
}
