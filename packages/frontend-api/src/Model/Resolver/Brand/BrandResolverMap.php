<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Brand;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;

class BrandResolverMap extends ResolverMap
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    protected $friendlyUrlFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(
        Domain $domain,
        FriendlyUrlFacade $friendlyUrlFacade
    ) {
        $this->domain = $domain;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
    }

    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'Brand' => [
                'link' => function ($data) {
                    $brandId = $data instanceof Brand ? $data->getId() : $data['id'];
                    return $this->getBrandMainLink($brandId);
                },
            ],
        ];
    }

    /**
     * @param int $brandId
     * @return string|null
     */
    protected function getBrandMainLink(int $brandId): ?string
    {
        $friendlyUrl = $this->friendlyUrlFacade->findMainFriendlyUrl($this->domain->getId(), 'front_brand_detail', $brandId);

        if ($friendlyUrl !== null) {
            return $this->friendlyUrlFacade->getAbsoluteUrlByFriendlyUrl($friendlyUrl);
        }

        return null;
    }
}
