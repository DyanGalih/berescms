<?php

namespace System\Tests\Console;

use System\Tests\Bootstrap\TestCase;

class PluginInstallTest extends TestCase
{
    public function testRejectsLegacyDotNotation()
    {
        $this->artisan('plugin:install Winter.Blog')
            ->expectsOutput('Invalid package name "Winter.Blog". Please specify a valid Composer package name in "vendor/package" format.')
            ->assertExitCode(1);
    }

    public function testRejectsSingleWord()
    {
        $this->artisan('plugin:install invalidname')
            ->expectsOutput('Invalid package name "invalidname". Please specify a valid Composer package name in "vendor/package" format.')
            ->assertExitCode(1);
    }

    public function testRejectsInvalidCharacters()
    {
        $this->artisan('plugin:install "invalid/package;rm -rf"')
            ->assertExitCode(1);
    }
}
