<?php

namespace SS6\ShopBundle\Controller\Front;

use SS6\ShopBundle\Component\Category\CurrentCategoryResolver;
use SS6\ShopBundle\Component\Controller\FrontBaseController;
use SS6\ShopBundle\Component\Domain\Domain;
use SS6\ShopBundle\Model\Category\CategoryFacade;
use SS6\ShopBundle\Model\Category\TopCategory\TopCategoryFacade;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends FrontBaseController {

	/**
	 * @var \SS6\ShopBundle\Model\Category\CategoryFacade
	 */
	private $categoryFacade;

	/**
	 * @var \SS6\ShopBundle\Component\Domain\Domain
	 */
	private $domain;

	/**
	 * @var \SS6\ShopBundle\Component\Category\CurrentCategoryResolver
	 */
	private $currentCategoryResolver;

	/**
	 * @var \SS6\ShopBundle\Model\Category\TopCategory\TopCategoryFacade
	 */
	private $topCategoryFacade;

	public function __construct(
		Domain $domain,
		CategoryFacade $categoryFacade,
		CurrentCategoryResolver $currentCategoryResolver,
		TopCategoryFacade $topCategoryFacade
	) {
		$this->domain = $domain;
		$this->categoryFacade = $categoryFacade;
		$this->currentCategoryResolver = $currentCategoryResolver;
		$this->topCategoryFacade = $topCategoryFacade;
	}

	/**
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 */
	public function panelAction(Request $request) {
		$categoryDetails = $this->categoryFacade->getVisibleLazyLoadedCategoryDetailsForParent(
			$this->categoryFacade->getRootCategory(),
			$this->domain->getCurrentDomainConfig()
		);
		$currentCategory = $this->currentCategoryResolver->findCurrentCategoryByRequest($request, $this->domain->getId());

		if ($currentCategory !== null) {
			$openCategories = $this->categoryFacade->getVisibleCategoriesInPathFromRootOnDomain(
				$currentCategory,
				$this->domain->getId()
			);
		} else {
			$openCategories = [];
		}

		return $this->render('@SS6Shop/Front/Content/Category/panel.html.twig', [
			'lazyLoadedCategoryDetails' => $categoryDetails,
			'isFirstLevel' => true,
			'openCategories' => $openCategories,
			'currentCategory' => $currentCategory,
		]);
	}

	/**
	 * @param int $parentCategoryId
	 */
	public function branchAction($parentCategoryId) {
		$parentCategory = $this->categoryFacade->getById($parentCategoryId);

		$categoryDetails = $this->categoryFacade->getVisibleLazyLoadedCategoryDetailsForParent(
			$parentCategory,
			$this->domain->getCurrentDomainConfig()
		);

		return $this->render('@SS6Shop/Front/Content/Category/panel.html.twig', [
			'lazyLoadedCategoryDetails' => $categoryDetails,
			'isFirstLevel' => false,
			'openCategories' => [],
			'currentCategory' => null,
		]);
	}

	public function topAction() {
		return $this->render('@SS6Shop/Front/Content/Category/top.html.twig', [
			'categories' => $this->topCategoryFacade->getCategoriesForAll($this->domain->getId()),
		]);
	}

}
