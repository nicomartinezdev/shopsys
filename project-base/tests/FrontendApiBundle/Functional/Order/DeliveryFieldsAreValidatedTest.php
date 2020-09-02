<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class DeliveryFieldsAreValidatedTest extends AbstractOrderTestCase
{
    public function testValidationErrorWhenCompanyBehalfIsTrueAndFieldsAreMissing(): void
    {
        $firstDomainLocale = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale();
        $expectedValidations = [
            'input.deliveryFirstName' => [
                0 => [
                    'message' => t('Please enter first name of contact person', [], 'validators', $firstDomainLocale),
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                ],
            ],
            'input.deliveryLastName' => [
                0 => [
                    'message' => t('Please enter last name of contact person', [], 'validators', $firstDomainLocale),
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                ],
            ],
            'input.deliveryStreet' => [
                0 => [
                    'message' => t('Please enter street', [], 'validators', $firstDomainLocale),
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                ],
            ],
            'input.deliveryCity' => [
                0 => [
                    'message' => t('Please enter city', [], 'validators', $firstDomainLocale),
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                ],
            ],
            'input.deliveryPostcode' => [
                0 => [
                    'message' => t('Please enter zip code', [], 'validators', $firstDomainLocale),
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                ],
            ],
            'input.deliveryCountry' => [
                0 => [
                    'message' => t('Please choose country', [], 'validators', $firstDomainLocale),
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                ],
            ],
        ];

        $orderMutation = $this->getOrderMutation(__DIR__ . '/Resources/deliveryFieldsAreValidated.graphql');

        $response = $this->getResponseContentForQuery($orderMutation);
        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);

        $this->assertEquals($expectedValidations, $this->getErrorsExtensionValidationFromResponse($response));
    }
}
