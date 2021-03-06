<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Listed;

use BadMethodCallException;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;
use Shopsys\ReadModelBundle\Image\ImageView;
use Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface;
use Shopsys\ReadModelBundle\Product\Action\ProductActionView;
use Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacadeInterface;

class ListedProductViewFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade
     */
    protected $productCachedAttributesFacade;

    /**
     * @var \Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface
     */
    protected $imageViewFacade;

    /**
     * @var \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacadeInterface
     */
    protected $productActionViewFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     * @param \Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface|null $imageViewFacade
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacadeInterface|null $productActionViewFacade
     */
    public function __construct(
        Domain $domain,
        ProductCachedAttributesFacade $productCachedAttributesFacade,
        ?ImageViewFacadeInterface $imageViewFacade = null,
        ?ProductActionViewFacadeInterface $productActionViewFacade = null
    ) {
        $this->domain = $domain;
        $this->productCachedAttributesFacade = $productCachedAttributesFacade;
        $this->imageViewFacade = $imageViewFacade;
        $this->productActionViewFacade = $productActionViewFacade;
    }

    /**
     * @param int $id
     * @param string $name
     * @param string|null $shortDescription
     * @param string $availability
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice $sellingPrice
     * @param array $flagIds
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionView $action
     * @param \Shopsys\ReadModelBundle\Image\ImageView|null $image
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView
     */
    protected function create(
        int $id,
        string $name,
        ?string $shortDescription,
        string $availability,
        ProductPrice $sellingPrice,
        array $flagIds,
        ProductActionView $action,
        ?ImageView $image
    ): ListedProductView {
        return new ListedProductView($id, $name, $shortDescription, $availability, $sellingPrice, $flagIds, $action, $image);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\ReadModelBundle\Image\ImageView|null $imageView
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionView $productActionView
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView
     */
    public function createFromProduct(Product $product, ?ImageView $imageView, ProductActionView $productActionView): ListedProductView
    {
        return $this->create(
            $product->getId(),
            $product->isVariant() && $product->getVariantAlias() ? $product->getVariantAlias() : $product->getName(),
            $product->getShortDescription($this->domain->getId()),
            $product->getCalculatedAvailability()->getName(),
            $this->productCachedAttributesFacade->getProductSellingPrice($product),
            $this->getFlagIdsForProduct($product),
            $productActionView,
            $imageView
        );
    }

    /**
     * @param array $productArray
     * @param \Shopsys\ReadModelBundle\Image\ImageView|null $imageView
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionView $productActionView
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView
     */
    public function createFromArray(array $productArray, ?ImageView $imageView, ProductActionView $productActionView, PricingGroup $pricingGroup): ListedProductView
    {
        return $this->create(
            $productArray['id'],
            $productArray['name'],
            $productArray['short_description'],
            $productArray['availability'],
            $this->getProductPriceFromArrayByPricingGroup($productArray['prices'], $pricingGroup),
            $productArray['flags'],
            $productActionView,
            $imageView
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView[]
     */
    public function createFromProducts(array $products): array
    {
        $imageViews = $this->imageViewFacade->getMainImagesByEntityIds(Product::class, $this->getIdsForProducts($products));
        $productActionViews = $this->productActionViewFacade->getForProducts($products);

        $listedProductViews = [];
        foreach ($products as $product) {
            $productId = $product->getId();
            $listedProductViews[$productId] = $this->createFromProduct($product, $imageViews[$productId], $productActionViews[$productId]);
        }

        return $listedProductViews;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @return int[]
     */
    protected function getIdsForProducts(array $products): array
    {
        return array_map(static function (Product $product): int {
            return $product->getId();
        }, $products);
    }

    /**
     * @param array $pricesArray
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice|null
     */
    protected function getProductPriceFromArrayByPricingGroup(array $pricesArray, PricingGroup $pricingGroup): ?ProductPrice
    {
        foreach ($pricesArray as $priceArray) {
            if ($priceArray['pricing_group_id'] === $pricingGroup->getId()) {
                $priceWithoutVat = Money::create((string)$priceArray['price_without_vat']);
                $priceWithVat = Money::create((string)$priceArray['price_with_vat']);
                $price = new Price($priceWithoutVat, $priceWithVat);
                return new ProductPrice($price, $priceArray['price_from']);
            }
        }

        return null;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return int[]
     */
    protected function getFlagIdsForProduct(Product $product): array
    {
        $flagIds = [];
        foreach ($product->getFlags() as $flag) {
            $flagIds[] = $flag->getId();
        }

        return $flagIds;
    }

    /**
     * @required
     * @param \Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface $imageViewFacade
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setImageViewFacade(ImageViewFacadeInterface $imageViewFacade): void
    {
        if ($this->imageViewFacade !== null && $this->imageViewFacade !== $imageViewFacade) {
            throw new BadMethodCallException(sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__));
        }
        if ($this->imageViewFacade === null) {
            @trigger_error(sprintf('The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.', __METHOD__), E_USER_DEPRECATED);
            $this->imageViewFacade = $imageViewFacade;
        }
    }

    /**
     * @required
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacadeInterface $productActionViewFacade
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setProductActionViewFacade(ProductActionViewFacadeInterface $productActionViewFacade): void
    {
        if ($this->productActionViewFacade !== null && $this->productActionViewFacade !== $productActionViewFacade) {
            throw new BadMethodCallException(sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__));
        }
        if ($this->productActionViewFacade === null) {
            @trigger_error(sprintf('The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.', __METHOD__), E_USER_DEPRECATED);
            $this->productActionViewFacade = $productActionViewFacade;
        }
    }
}
