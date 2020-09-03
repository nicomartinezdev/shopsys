<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Transport;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

class TransportResolverMap extends ResolverMap
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    private $currencyFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Rounding
     */
    private $rounding;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Rounding $rounding
     */
    public function __construct(Domain $domain, CurrencyFacade $currencyFacade, Rounding $rounding)
    {
        $this->domain = $domain;
        $this->currencyFacade = $currencyFacade;
        $this->rounding = $rounding;
    }

    protected function map()
    {
        $domainId = $this->domain->getId();
        $currencyFacade = $this->currencyFacade;
        $rounding = $this->rounding;
        return [
            'Transport' => [
                'price' => function (Transport $transport) use ($domainId, $currencyFacade, $rounding) {
                    $currency = $currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
                    return [
                        'priceWithVat' => $rounding->roundPriceWithVatByCurrency($transport->getPrice($domainId)->getPrice(), $currency)->getAmount(),
                        'priceWithoutVat' => $rounding->roundPriceWithoutVat(),
                        'vatAmount' => '',
                    ];
                },
            ],
        ];
    }
}
