<?php

namespace System\Tests\Console;

use System\Tests\Bootstrap\TestCase;

class PluginInstallTest extends TestCase
{
    public function testRejectsLegacyDotNotation()
    {
        $this->artisan('plugin:install Winter.Blog')
            ->expectsOutputToContain('Invalid package name "Winter.Blog"')
            ->assertExitCode(1);
    }

    public function testRejectsSingleWord()
    {
        $this->artisan('plugin:install invalidname')
            ->expectsOutputToContain('Invalid package name "invalidname"')
            ->assertExitCode(1);
    }

    public function testRejectsInvalidCharacters()
    {
        $this->artisan('plugin:install "invalid/package;rm -rf"')
            ->assertExitCode(1);
    }
}
