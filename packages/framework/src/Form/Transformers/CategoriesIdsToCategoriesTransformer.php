<?php

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;
use Symfony\Component\Form\DataTransformerInterface;

class CategoriesIdsToCategoriesTransformer implements DataTransformerInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryRepository $categoryRepository
     */
    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[]|null $categories
     * @return int[]
     */
    public function transform($categories)
    {
        $categoriesIds = [];

        if (is_iterable($categories)) {
            foreach ($categories as $category) {
                $categoriesIds[] = $category->getId();
            }
        }

        return $categoriesIds;
    }

    /**
     * @param int[] $categoriesIds
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]|null
     */
    public function reverseTransform($categoriesIds)
    {
        $categories = [];

        if (is_array($categoriesIds)) {
            foreach ($categoriesIds as $categoryId) {
                try {
                    $categories[] = $this->categoryRepository->getById($categoryId);
                } catch (\Shopsys\FrameworkBundle\Model\Category\Exception\CategoryNotFoundException $e) {
                    throw new \Symfony\Component\Form\Exception\TransformationFailedException('Category not found', 0, $e);
                }
            }
        }

        return $categories;
    }
}
