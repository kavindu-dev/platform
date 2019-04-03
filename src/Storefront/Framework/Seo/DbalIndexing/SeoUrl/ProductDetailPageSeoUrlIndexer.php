<?php declare(strict_types=1);

namespace Shopware\Storefront\Framework\Seo\DbalIndexing\SeoUrl;

use Shopware\Core\Checkout\Context\CheckoutContextFactoryInterface;
use Shopware\Core\Content\Product\Util\EventIdExtractor;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Storefront\Framework\Seo\SeoService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductDetailPageSeoUrlIndexer extends SeoUrlIndexer
{
    public const ROUTE_NAME = 'frontend.detail.page';

    /**
     * @var EventIdExtractor
     */
    private $idExtractor;

    public function __construct(
        EntityRepositoryInterface $salesChannelRepository,
        EventDispatcherInterface $eventDispatcher,
        SeoService $seoService,
        CheckoutContextFactoryInterface $checkoutContextFactory,
        EntityRepositoryInterface $entityRepository,
        EventIdExtractor $idExtractor
    ) {
        parent::__construct(
            $salesChannelRepository,
            $eventDispatcher,
            $seoService,
            $checkoutContextFactory,
            self::ROUTE_NAME,
            $entityRepository
        );
        $this->idExtractor = $idExtractor;
    }

    public function extractIds(EntityWrittenContainerEvent $event): array
    {
        return $this->idExtractor->getProductIds($event);
    }
}