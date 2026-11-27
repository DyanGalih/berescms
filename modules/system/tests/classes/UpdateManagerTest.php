<?php

namespace System\Tests\Classes;

use ApplicationException;
use System\Classes\UpdateManager;
use System\Tests\Bootstrap\TestCase;

class UpdateManagerTest extends TestCase
{
    protected UpdateManager $manager;

    public function setUp(): void
    {
        parent::setUp();
        $this->manager = UpdateManager::instance();
    }

    public function testGetHashReturnsSha256Default()
    {
        $hash = $this->manager->getHash();
        $this->assertIsString($hash);
        $this->assertEquals(64, strlen($hash));
    }

    public function testMarketplaceDownloadCoreThrowsApplicationException()
    {
        $this->expectException(ApplicationException::class);
        $this->expectExceptionMessage('Proprietary marketplace and server download protocols have been retired in BeresCMS');
        $this->manager->downloadCore('testhash');
    }

    public function testMarketplaceExtractCoreThrowsApplicationException()
    {
        $this->expectException(ApplicationException::class);
        $this->expectExceptionMessage('Proprietary marketplace and server download protocols have been retired in BeresCMS');
        $this->manager->extractCore();
    }

    public function testMarketplaceDownloadPluginThrowsApplicationException()
    {
        $this->expectException(ApplicationException::class);
        $this->expectExceptionMessage('Proprietary marketplace and server download protocols have been retired in BeresCMS');
        $this->manager->downloadPlugin('Author.Plugin', 'testhash');
    }

    public function testMarketplaceExtractPluginThrowsApplicationException()
    {
        $this->expectException(ApplicationException::class);
        $this->expectExceptionMessage('Proprietary marketplace and server download protocols have been retired in BeresCMS');
        $this->manager->extractPlugin('Author.Plugin', 'testhash');
    }

    public function testMarketplaceDownloadThemeThrowsApplicationException()
    {
        $this->expectException(ApplicationException::class);
        $this->expectExceptionMessage('Proprietary marketplace and server download protocols have been retired in BeresCMS');
        $this->manager->downloadTheme('test-theme', 'testhash');
    }

    public function testMarketplaceExtractThemeThrowsApplicationException()
    {
        $this->expectException(ApplicationException::class);
        $this->expectExceptionMessage('Proprietary marketplace and server download protocols have been retired in BeresCMS');
        $this->manager->extractTheme('test-theme', 'testhash');
    }

    public function testMarketplaceServerFileThrowsApplicationException()
    {
        $this->expectException(ApplicationException::class);
        $this->expectExceptionMessage('Proprietary marketplace and server download protocols have been retired in BeresCMS');
        $this->manager->requestServerFile('test/uri', 'code', 'hash');
    }

    public function testMarketplaceQueriesReturnEmptySafely()
    {
        $this->assertSame([], $this->manager->requestServerData('plugin/detail'));
        $this->assertSame([], $this->manager->requestProductDetails(['Test.Plugin']));
        $this->assertSame([], $this->manager->requestPopularProducts('plugin'));
        $this->assertSame([], $this->manager->requestPluginDetails('Test.Plugin'));
        $this->assertSame([], $this->manager->requestPluginContent('Test.Plugin'));
        $this->assertSame([], $this->manager->requestThemeDetails('test-theme'));
        $this->assertSame([], $this->manager->requestProjectDetails('test-project'));
        $this->assertSame([], $this->manager->requestChangelog());
    }
}
