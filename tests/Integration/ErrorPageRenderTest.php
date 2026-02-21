<?php

declare(strict_types=1);

namespace Symkit\ErrorBundle\Tests\Integration;

use Nyholm\BundleTest\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symkit\ErrorBundle\SymkitErrorBundle;
use Twig\Environment;

final class ErrorPageRenderTest extends KernelTestCase
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
        $kernel->addTestConfig(static function ($container): void {
            $container->loadFromExtension('framework', ['test' => true]);
        });
        $kernel->handleOptions($options);

        return $kernel;
    }

    public function test404TemplateRendersWithExpectedContent(): void
    {
        self::bootKernel();

        $requestStack = self::getContainer()->get('request_stack');
        $requestStack->push(Request::create('/_test-404', 'GET', [], [], [], ['REQUEST_URI' => '/_test-404']));

        /** @var Environment $twig */
        $twig = self::getContainer()->get('twig');
        $content = $twig->render('@Twig/Exception/error404.html.twig');

        self::assertStringContainsString('error-container', $content);
        self::assertStringContainsString('noindex', $content);
    }

    public function test503TemplateRendersWithExpectedContent(): void
    {
        self::bootKernel();

        $requestStack = self::getContainer()->get('request_stack');
        $requestStack->push(Request::create('/_test-503', 'GET'));

        /** @var Environment $twig */
        $twig = self::getContainer()->get('twig');
        $content = $twig->render('@Twig/Exception/error503.html.twig');

        self::assertStringContainsString('error-container', $content);
        self::assertStringContainsString('noindex', $content);
    }
}
