<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class CompanyFieldsAreValidatedTest extends AbstractOrderTestCase
{
    public function testValidationErrorWhenCompanyBehalfIsTrueAndFieldsAreMissing(): void
    {
        $firstDomainLocale = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale();
        $expectedValidations = [
            'input.companyName' => [
                0 => [
                    'message' => t('Please enter company name', [], 'validators', $firstDomainLocale),
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                ],
            ],
            'input.companyNumber' => [
                0 => [
                    'message' => t('Please enter identification number', [], 'validators', $firstDomainLocale),
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                ],
            ],
        ];

        $orderMutation = $this->getOrderMutation(__DIR__ . '/Resources/companyFieldsAreValidated.graphql');

        $response = $this->getResponseContentForQuery($orderMutation);
        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);

        $this->assertEquals($expectedValidations, $this->getErrorsExtensionValidationFromResponse($response));
    }
}
