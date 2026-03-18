<?php

declare(strict_types=1);

namespace Symkit\ErrorBundle\Tests\Integration;

use Nyholm\BundleTest\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symkit\ErrorBundle\SymkitErrorBundle;
use Symkit\ErrorBundle\Tests\Support\TwigTestKernelConfig;
use Twig\Environment;

final class TwigPrependTest extends KernelTestCase
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
        $kernel->addTestBundle(TwigBundle::class);
        $kernel->addTestBundle(SymkitErrorBundle::class);
        $kernel->addTestConfig(TwigTestKernelConfig::frameworkTestWithTwigIntl());
        $kernel->handleOptions($options);

        return $kernel;
    }

    public function testBundleMetadataIsRegistered(): void
    {
        self::bootKernel();

        /** @var array<string, array<string, string>> $metadata */
        $metadata = self::getContainer()->getParameter('kernel.bundles_metadata');
        self::assertArrayHasKey('SymkitErrorBundle', $metadata);
    }

    public function testBundleExceptionTemplatePathIsRegistered(): void
    {
        self::bootKernel();

        /** @var Environment $twig */
        $twig = self::getContainer()->get('twig');
        $content = $twig->render('@Twig/Exception/base_error.html.twig');

        self::assertStringContainsString('error-container', $content, 'Bundle template path must be prepended so @Twig/Exception/base_error is found');
    }

    public function testTwigGlobalWebsiteNameIsSet(): void
    {
        self::bootKernel();

        /** @var Environment $twig */
        $twig = self::getContainer()->get('twig');
        $globals = $twig->getGlobals();

        self::assertArrayHasKey('symkit_error_website_name', $globals);
        self::assertSame('Symkit', $globals['symkit_error_website_name']);
    }

    public function testTwigGlobalWebsiteNameReflectsConfig(): void
    {
        /** @var TestKernel $kernel */
        $kernel = self::createKernel();
        $kernel->addTestConfig(TwigTestKernelConfig::frameworkTestWithTwigIntl(static function ($container): void {
            $container->loadFromExtension('symkit_error', [
                'website_name' => 'Custom Name',
            ]);
        }));
        $kernel->boot();

        /** @var Environment $twig */
        $twig = $kernel->getContainer()->get('test.service_container')->get('twig');
        $globals = $twig->getGlobals();

        self::assertArrayHasKey('symkit_error_website_name', $globals);
        self::assertSame('Custom Name', $globals['symkit_error_website_name']);
    }

    public function testWhenDisabledTemplatePathAndGlobalAreNotRegistered(): void
    {
        /** @var TestKernel $kernel */
        $kernel = self::createKernel();
        $kernel->addTestConfig(static function ($container): void {
            $container->loadFromExtension('framework', ['test' => true]);
            $container->loadFromExtension('symkit_error', ['enabled' => false]);
        });
        $kernel->boot();

        /** @var Environment $twig */
        $twig = $kernel->getContainer()->get('test.service_container')->get('twig');
        $globals = $twig->getGlobals();

        self::assertArrayNotHasKey('symkit_error_website_name', $globals);
        self::assertArrayNotHasKey('symkit_error_home_path', $globals);
    }

    public function testTwigGlobalHomePathReflectsConfig(): void
    {
        /** @var TestKernel $kernel */
        $kernel = self::createKernel();
        $kernel->addTestConfig(TwigTestKernelConfig::frameworkTestWithTwigIntl(static function ($container): void {
            $container->loadFromExtension('symkit_error', [
                'home_path' => '/app',
            ]);
        }));
        $kernel->boot();

        /** @var Environment $twig */
        $twig = $kernel->getContainer()->get('test.service_container')->get('twig');
        $globals = $twig->getGlobals();

        self::assertArrayHasKey('symkit_error_home_path', $globals);
        self::assertSame('/app', $globals['symkit_error_home_path']);
    }

    public function testTwigGlobalsMatchContainerParametersWhenSeveralConfigFilesAreLoaded(): void
    {
        /** @var TestKernel $kernel */
        $kernel = self::createKernel();
        $kernel->addTestConfig(TwigTestKernelConfig::frameworkTestWithTwigIntl(static function ($container): void {
            $container->loadFromExtension('symkit_error', [
                'website_name' => 'First',
                'home_path' => '/first',
            ]);
            $container->loadFromExtension('symkit_error', [
                'website_name' => 'Second',
            ]);
        }));
        $kernel->boot();

        $c = $kernel->getContainer()->get('test.service_container');
        self::assertSame('Second', $c->getParameter('symkit_error.website_name'));
        self::assertSame('/first', $c->getParameter('symkit_error.home_path'));
        self::assertTrue($c->getParameter('symkit_error.enabled'));

        /** @var Environment $twig */
        $twig = $c->get('twig');
        $globals = $twig->getGlobals();
        self::assertSame($c->getParameter('symkit_error.website_name'), $globals['symkit_error_website_name']);
        self::assertSame($c->getParameter('symkit_error.home_path'), $globals['symkit_error_home_path']);
    }

    public function testLastConfigSliceCanDisableTwigPrependWhileKeepingMergedParameters(): void
    {
        /** @var TestKernel $kernel */
        $kernel = self::createKernel();
        $kernel->addTestConfig(static function ($container): void {
            $container->loadFromExtension('framework', ['test' => true]);
            $container->loadFromExtension('symkit_error', [
                'website_name' => 'Brand',
                'home_path' => '/shop',
            ]);
            $container->loadFromExtension('symkit_error', [
                'enabled' => false,
            ]);
        });
        $kernel->boot();

        $c = $kernel->getContainer()->get('test.service_container');
        self::assertFalse($c->getParameter('symkit_error.enabled'));
        self::assertSame('Brand', $c->getParameter('symkit_error.website_name'));
        self::assertSame('/shop', $c->getParameter('symkit_error.home_path'));

        /** @var Environment $twig */
        $twig = $c->get('twig');
        $globals = $twig->getGlobals();
        self::assertArrayNotHasKey('symkit_error_website_name', $globals);
        self::assertArrayNotHasKey('symkit_error_home_path', $globals);
    }

    public function testTwigGlobalHomePathSanitizedWhenConfigIsMalicious(): void
    {
        /** @var TestKernel $kernel */
        $kernel = self::createKernel();
        $kernel->addTestConfig(TwigTestKernelConfig::frameworkTestWithTwigIntl(static function ($container): void {
            $container->loadFromExtension('symkit_error', [
                'home_path' => 'javascript:void(0)',
            ]);
        }));
        $kernel->boot();

        $c = $kernel->getContainer()->get('test.service_container');
        self::assertSame('/', $c->getParameter('symkit_error.home_path'));
        $twig = $c->get('twig');
        self::assertSame('/', $twig->getGlobals()['symkit_error_home_path']);
    }
}
