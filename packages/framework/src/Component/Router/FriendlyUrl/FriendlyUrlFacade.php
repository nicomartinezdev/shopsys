<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;

class FriendlyUrlFacade
{
    protected const MAX_URL_UNIQUE_RESOLVE_ATTEMPT = 100;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory
     */
    protected $domainRouterFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlUniqueResultFactory
     */
    protected $friendlyUrlUniqueResultFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository
     */
    protected $friendlyUrlRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFactoryInterface
     */
    protected $friendlyUrlFactory;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlUniqueResultFactory $friendlyUrlUniqueResultFactory
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFactoryInterface $friendlyUrlFactory
     */
    public function __construct(
        EntityManagerInterface $em,
        DomainRouterFactory $domainRouterFactory,
        FriendlyUrlUniqueResultFactory $friendlyUrlUniqueResultFactory,
        FriendlyUrlRepository $friendlyUrlRepository,
        Domain $domain,
        FriendlyUrlFactoryInterface $friendlyUrlFactory
    ) {
        $this->em = $em;
        $this->domainRouterFactory = $domainRouterFactory;
        $this->friendlyUrlUniqueResultFactory = $friendlyUrlUniqueResultFactory;
        $this->friendlyUrlRepository = $friendlyUrlRepository;
        $this->domain = $domain;
        $this->friendlyUrlFactory = $friendlyUrlFactory;
    }

    /**
     * @param string $routeName
     * @param int $entityId
     * @param string[] $namesByLocale
     */
    public function createFriendlyUrls($routeName, $entityId, array $namesByLocale)
    {
        $friendlyUrls = $this->friendlyUrlFactory->createForAllDomains($routeName, $entityId, $namesByLocale);
        foreach ($friendlyUrls as $friendlyUrl) {
            $locale = $this->domain->getDomainConfigById($friendlyUrl->getDomainId())->getLocale();
            $this->resolveUniquenessOfFriendlyUrlAndFlush($friendlyUrl, $namesByLocale[$locale]);
        }
    }

    /**
     * @param string $routeName
     * @param int $entityId
     * @param string $entityName
     * @param int $domainId
     */
    public function createFriendlyUrlForDomain($routeName, $entityId, $entityName, $domainId)
    {
        $friendlyUrl = $this->friendlyUrlFactory->createIfValid($routeName, $entityId, (string)$entityName, $domainId);
        if ($friendlyUrl !== null) {
            $this->resolveUniquenessOfFriendlyUrlAndFlush($friendlyUrl, $entityName);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl $friendlyUrl
     * @param string $entityName
     */
    protected function resolveUniquenessOfFriendlyUrlAndFlush(FriendlyUrl $friendlyUrl, $entityName)
    {
        $attempt = 0;
        do {
            $attempt++;
            if ($attempt > static::MAX_URL_UNIQUE_RESOLVE_ATTEMPT) {
                throw new \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\ReachMaxUrlUniqueResolveAttemptException(
                    $friendlyUrl,
                    $attempt
                );
            }

            $domainRouter = $this->domainRouterFactory->getRouter($friendlyUrl->getDomainId());
            try {
                $matchedRouteData = $domainRouter->match('/' . $friendlyUrl->getSlug());
            } catch (\Symfony\Component\Routing\Exception\ResourceNotFoundException $e) {
                $matchedRouteData = null;
            }

            $friendlyUrlUniqueResult = $this->friendlyUrlUniqueResultFactory->create(
                $attempt,
                $friendlyUrl,
                (string)$entityName,
                $matchedRouteData
            );
            $friendlyUrl = $friendlyUrlUniqueResult->getFriendlyUrlForPersist();
        } while (!$friendlyUrlUniqueResult->isUnique());

        if ($friendlyUrl !== null) {
            $this->em->persist($friendlyUrl);
            $this->em->flush($friendlyUrl);
            $this->setFriendlyUrlAsMain($friendlyUrl);
        }
    }

    /**
     * @param string $routeName
     * @param int $entityId
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl[]
     */
    public function getAllByRouteNameAndEntityId($routeName, $entityId)
    {
        return $this->friendlyUrlRepository->getAllByRouteNameAndEntityId($routeName, $entityId);
    }

    /**
     * @param int $domainId
     * @param string $routeName
     * @param int $entityId
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl|null
     */
    public function findMainFriendlyUrl($domainId, $routeName, $entityId)
    {
        return $this->friendlyUrlRepository->findMainFriendlyUrl($domainId, $routeName, $entityId);
    }

    /**
     * @param int $domainId
     * @param string $routeName
     * @param int $entityId
     * @return string
     */
    public function getAbsoluteUrlByRouteNameAndEntityId(int $domainId, string $routeName, int $entityId): string
    {
        $mainFriendlyUrl = $this->findMainFriendlyUrl($domainId, $routeName, $entityId);

        if ($mainFriendlyUrl === null) {
            throw new FriendlyUrlNotFoundException();
        }

        return $this->getAbsoluteUrlByFriendlyUrl($mainFriendlyUrl);
    }

    /**
     * @param string $routeName
     * @param int $entityId
     * @return string
     */
    public function getAbsoluteUrlByRouteNameAndEntityIdOnCurrentDomain(string $routeName, int $entityId): string
    {
        return $this->getAbsoluteUrlByRouteNameAndEntityId($this->domain->getId(), $routeName, $entityId);
    }

    /**
     * @param string $routeName
     * @param int $entityId
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData $urlListData
     */
    public function saveUrlListFormData($routeName, $entityId, UrlListData $urlListData)
    {
        $toFlush = [];

        foreach ($urlListData->mainFriendlyUrlsByDomainId as $friendlyUrl) {
            if ($friendlyUrl !== null) {
                $this->setFriendlyUrlAsMain($friendlyUrl);
                $toFlush[] = $friendlyUrl;
            }
        }

        foreach ($urlListData->toDelete as $friendlyUrls) {
            foreach ($friendlyUrls as $friendlyUrl) {
                if (!$friendlyUrl->isMain()) {
                    $this->em->remove($friendlyUrl);
                    $toFlush[] = $friendlyUrl;
                }
            }
        }

        foreach ($urlListData->newUrls as $urlData) {
            $domainId = $urlData[UrlListData::FIELD_DOMAIN];
            $newSlug = $urlData[UrlListData::FIELD_SLUG];
            $newFriendlyUrl = $this->friendlyUrlFactory->create($routeName, $entityId, $domainId, $newSlug);
            $this->em->persist($newFriendlyUrl);
            $toFlush[] = $newFriendlyUrl;
        }

        if (count($toFlush) > 0) {
            $this->em->flush($toFlush);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl $mainFriendlyUrl
     */
    protected function setFriendlyUrlAsMain(FriendlyUrl $mainFriendlyUrl)
    {
        $friendlyUrls = $this->friendlyUrlRepository->getAllByRouteNameAndEntityIdAndDomainId(
            $mainFriendlyUrl->getRouteName(),
            $mainFriendlyUrl->getEntityId(),
            $mainFriendlyUrl->getDomainId()
        );
        foreach ($friendlyUrls as $friendlyUrl) {
            $friendlyUrl->setMain(false);
        }
        $mainFriendlyUrl->setMain(true);

        $this->em->flush($friendlyUrls);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl $friendlyUrl
     * @return string
     */
    public function getAbsoluteUrlByFriendlyUrl(FriendlyUrl $friendlyUrl): string
    {
        $domainConfig = $this->domain->getDomainConfigById($friendlyUrl->getDomainId());

        return $domainConfig->getUrl() . '/' . $friendlyUrl->getSlug();
    }

    /**
     * @param string $routeName
     * @param int $entityId
     */
    public function removeFriendlyUrlsForAllDomains(string $routeName, int $entityId): void
    {
        foreach ($this->getAllByRouteNameAndEntityId($routeName, $entityId) as $friendlyUrl) {
            $this->em->remove($friendlyUrl);
        }

        $this->em->flush();
    }
}
