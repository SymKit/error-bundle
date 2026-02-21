<?php

declare(strict_types=1);

namespace Symkit\ErrorBundle\Tests\Integration;

use Nyholm\BundleTest\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Symkit\ErrorBundle\SymkitErrorBundle;

final class ConfigurationTest extends KernelTestCase
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
        $kernel->handleOptions($options);

        return $kernel;
    }

    public function testDefaultWebsiteName(): void
    {
        /** @var TestKernel $kernel */
        $kernel = self::createKernel();
        $kernel->addTestConfig(static function ($container): void {
            $container->loadFromExtension('framework', ['test' => true]);
        });
        $kernel->boot();

        self::assertSame('Symkit', $kernel->getContainer()->getParameter('symkit_error.website_name'));
    }

    public function testCustomWebsiteName(): void
    {
        /** @var TestKernel $kernel */
        $kernel = self::createKernel();
        $kernel->addTestConfig(static function ($container): void {
            $container->loadFromExtension('framework', ['test' => true]);
            $container->loadFromExtension('symkit_error', [
                'website_name' => 'Acme Corp',
            ]);
        });
        $kernel->boot();

        self::assertSame('Acme Corp', $kernel->getContainer()->getParameter('symkit_error.website_name'));
    }

    public function testDefaultEnabled(): void
    {
        /** @var TestKernel $kernel */
        $kernel = self::createKernel();
        $kernel->addTestConfig(static function ($container): void {
            $container->loadFromExtension('framework', ['test' => true]);
        });
        $kernel->boot();

        self::assertTrue($kernel->getContainer()->getParameter('symkit_error.enabled'));
    }

    public function testDisabled(): void
    {
        /** @var TestKernel $kernel */
        $kernel = self::createKernel();
        $kernel->addTestConfig(static function ($container): void {
            $container->loadFromExtension('framework', ['test' => true]);
            $container->loadFromExtension('symkit_error', ['enabled' => false]);
        });
        $kernel->boot();

        self::assertFalse($kernel->getContainer()->getParameter('symkit_error.enabled'));
    }

    public function testDefaultHomePath(): void
    {
        /** @var TestKernel $kernel */
        $kernel = self::createKernel();
        $kernel->addTestConfig(static function ($container): void {
            $container->loadFromExtension('framework', ['test' => true]);
        });
        $kernel->boot();

        self::assertSame('/', $kernel->getContainer()->getParameter('symkit_error.home_path'));
    }

    public function testCustomHomePath(): void
    {
        /** @var TestKernel $kernel */
        $kernel = self::createKernel();
        $kernel->addTestConfig(static function ($container): void {
            $container->loadFromExtension('framework', ['test' => true]);
            $container->loadFromExtension('symkit_error', [
                'home_path' => '/dashboard',
            ]);
        });
        $kernel->boot();

        self::assertSame('/dashboard', $kernel->getContainer()->getParameter('symkit_error.home_path'));
    }
}
