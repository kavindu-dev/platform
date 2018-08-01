<?php declare(strict_types=1);

namespace Shopware\Core\Content\Test\Product;

use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\ORM\RepositoryInterface;
use Shopware\Core\Framework\Test\Api\ApiTestCase;

class ProductControllerTest extends ApiTestCase
{
    /**
     * @var RepositoryInterface
     */
    private $productRepository;

    /**
     * @var Connection
     */
    private $connection;

    protected function setUp()
    {
        parent::setUp();

        $this->productRepository = self::$container->get('product.repository');
        $this->connection = self::$container->get(Connection::class);
        $this->connection->beginTransaction();
        $this->connection->executeUpdate('DELETE FROM product');
    }

    public function tearDown()
    {
        $this->connection->rollBack();
        parent::tearDown();
    }

    public function testProductList(): void
    {
        $manufacturerId = Uuid::uuid4()->getHex();
        $taxId = Uuid::uuid4()->getHex();

        $this->productRepository->create([
            [
                'id' => Uuid::uuid4()->getHex(),
                'name' => 'Test',
                'price' => ['gross' => 10, 'net' => 9],
                'manufacturer' => ['id' => $manufacturerId, 'name' => 'test'],
                'tax' => ['id' => $taxId, 'taxRate' => 17, 'name' => 'with id'],
            ],
        ], Context::createDefaultContext(Defaults::TENANT_ID));

        $this->storefrontApiClient->request('GET', '/storefront-api/product');

        self::assertSame(200, $this->storefrontApiClient->getResponse()->getStatusCode(), $this->storefrontApiClient->getResponse()->getContent());

        $content = json_decode($this->storefrontApiClient->getResponse()->getContent(), true);

        static::assertNotEmpty($content);
        static::assertArrayHasKey('total', $content);
        static::assertArrayHasKey('data', $content);
        static::assertGreaterThan(0, $content['total']);
        static::assertNotEmpty($content['data']);

        foreach ($content['data'] as $product) {
            static::assertArrayHasKey('calculatedListingPrice', $product);
            static::assertArrayHasKey('calculatedPriceRules', $product);
            static::assertArrayHasKey('calculatedPrice', $product);
            static::assertArrayHasKey('price', $product);
            static::assertArrayHasKey('name', $product);
            static::assertArrayHasKey('id', $product);
        }
    }

    public function testProductDetail(): void
    {
        $productId = Uuid::uuid4()->getHex();
        $manufacturerId = Uuid::uuid4()->toString();
        $taxId = Uuid::uuid4()->toString();

        $this->productRepository->create([
            [
                'id' => $productId,
                'name' => 'Test',
                'price' => ['gross' => 10, 'net' => 9],
                'manufacturer' => ['id' => $manufacturerId, 'name' => 'test'],
                'tax' => ['id' => $taxId, 'taxRate' => 17, 'name' => 'with id'],
            ],
        ], Context::createDefaultContext(Defaults::TENANT_ID));

        $this->storefrontApiClient->request('GET', '/storefront-api/product/' . $productId);

        self::assertSame(200, $this->storefrontApiClient->getResponse()->getStatusCode(), $this->storefrontApiClient->getResponse()->getContent());

        $content = json_decode($this->storefrontApiClient->getResponse()->getContent(), true);

        static::assertEquals($productId, $content['data']['id']);
        static::assertEquals(10, $content['data']['price']['gross']);
        static::assertEquals('test', $content['data']['manufacturer']['name']);
        static::assertEquals('with id', $content['data']['tax']['name']);
        static::assertEquals(17, $content['data']['tax']['taxRate']);
    }
}
