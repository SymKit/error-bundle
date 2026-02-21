<?php

declare(strict_types=1);

namespace Symkit\ErrorBundle\Tests\Integration;

use Nyholm\BundleTest\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Symkit\ErrorBundle\SymkitErrorBundle;

final class BundleBootTest extends KernelTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        restore_exception_handler();
    }

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        /** @var TestKernel $kernel */
        $kernel = parent::createKernel($options);
        $kernel->addTestBundle(SymkitErrorBundle::class);
        $kernel->addTestConfig(static function ($container): void {
            $container->loadFromExtension('framework', ['test' => true]);
        });
        $kernel->handleOptions($options);

        return $kernel;
    }

    public function testBundleBoots(): void
    {
        self::bootKernel();

        self::assertArrayHasKey(
            'SymkitErrorBundle',
            self::$kernel->getBundles(),
        );
    }

    public function testParametersAreRegistered(): void
    {
        self::bootKernel();

        self::assertTrue(self::getContainer()->hasParameter('symkit_error.enabled'));
        self::assertTrue(self::getContainer()->getParameter('symkit_error.enabled'));
        self::assertTrue(self::getContainer()->hasParameter('symkit_error.website_name'));
        self::assertSame('Symkit', self::getContainer()->getParameter('symkit_error.website_name'));
    }
}
