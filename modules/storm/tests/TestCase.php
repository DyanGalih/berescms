<?php

namespace Winter\Storm\Tests;

use ReflectionClass;
use Orchestra\Testbench\Foundation\PackageManifest;
use Orchestra\Testbench\TestCase as TestbenchTestCase;
use PHPUnit\Framework\Assert;
use Winter\Storm\Foundation\Application;

class TestCase extends TestbenchTestCase
{
    /**
     * Resolve application implementation.
     *
     * @return \Winter\Storm\Foundation\Application
     */
    protected function resolveApplication()
    {
        return new Application(static::applicationBasePath());
    }
    
    /**
     * Resolve application resolving callback.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationResolvingCallback($app): void
    {
        $app->bind(
            \Winter\Storm\Foundation\Bootstrap\LoadConfiguration::class,
            \Orchestra\Testbench\Bootstrap\LoadConfiguration::class
        );

        PackageManifest::swap($app, $this);
    }

    protected static function callProtectedMethod($object, $name, $params = [])
    {
        $className = get_class($object);
        $class = new ReflectionClass($className);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $params);
    }

    /**
     * Stub for `assertFileNotExists` to allow compatibility with both PHPUnit 8 and 9.
     *
     * @param string $filename
     * @param string $message
     * @return void
     */
    public static function assertFileNotExists(string $filename, string $message = ''): void
    {
        if (method_exists(Assert::class, 'assertFileDoesNotExist')) {
            Assert::assertFileDoesNotExist($filename, $message);
            return;
        }

        Assert::assertFileNotExists($filename, $message);
    }

    /**
     * Polyfill for assertArraySubset across all PHPUnit versions without external packages.
     */
    public static function assertArraySubset($subset, $array, bool $checkForObjectIdentity = false, string $message = ''): void
    {
        if (!is_iterable($subset)) {
            throw new \InvalidArgumentException('Argument #1 of assertArraySubset must be array or Traversable');
        }
        if (!is_iterable($array)) {
            throw new \InvalidArgumentException('Argument #2 of assertArraySubset must be array or Traversable');
        }

        foreach ($subset as $key => $value) {
            Assert::assertArrayHasKey($key, $array, $message);
            if (is_array($value) && is_array($array[$key])) {
                self::assertArraySubset($value, $array[$key], $checkForObjectIdentity, $message);
            } elseif ($checkForObjectIdentity) {
                Assert::assertSame($value, $array[$key], $message);
            } else {
                Assert::assertEquals($value, $array[$key], $message);
            }
        }
    }

    /**
     * Resolve application Console Kernel implementation.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationConsoleKernel($app)
    {
        $app->singleton(
            \Illuminate\Contracts\Console\Kernel::class,
            \Winter\Storm\Foundation\Console\Kernel::class
        );
    }

    /**
     * Resolve application HTTP Kernel implementation.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationHttpKernel($app)
    {
        $app->singleton(
            \Illuminate\Contracts\Http\Kernel::class,
            \Winter\Storm\Foundation\Http\Kernel::class
        );
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            /*
            * Laravel providers
            */
            \Illuminate\Broadcasting\BroadcastServiceProvider::class,
            \Illuminate\Bus\BusServiceProvider::class,
            \Illuminate\Cache\CacheServiceProvider::class,
            \Illuminate\Cookie\CookieServiceProvider::class,
            \Illuminate\Encryption\EncryptionServiceProvider::class,
            \Illuminate\Foundation\Providers\FoundationServiceProvider::class,
            \Illuminate\Hashing\HashServiceProvider::class,
            \Illuminate\Pagination\PaginationServiceProvider::class,
            \Illuminate\Pipeline\PipelineServiceProvider::class,
            \Illuminate\Queue\QueueServiceProvider::class,
            \Illuminate\Session\SessionServiceProvider::class,
            \Illuminate\View\ViewServiceProvider::class,
            \Laravel\Tinker\TinkerServiceProvider::class,

            /*
            * Winter Storm providers
            */
            \Winter\Storm\Foundation\Providers\ConsoleSupportServiceProvider::class,
            \Winter\Storm\Database\DatabaseServiceProvider::class,
            \Winter\Storm\Halcyon\HalcyonServiceProvider::class,
            \Winter\Storm\Filesystem\FilesystemServiceProvider::class,
            \Winter\Storm\Parse\ParseServiceProvider::class,
            \Winter\Storm\Html\HtmlServiceProvider::class,
            \Winter\Storm\Html\UrlServiceProvider::class,
            \Winter\Storm\Network\NetworkServiceProvider::class,
            \Winter\Storm\Flash\FlashServiceProvider::class,
            \Winter\Storm\Mail\MailServiceProvider::class,
            \Winter\Storm\Argon\ArgonServiceProvider::class,
            \Winter\Storm\Redis\RedisServiceProvider::class,
            \Winter\Storm\Validation\ValidationServiceProvider::class,
        ];
    }
}
