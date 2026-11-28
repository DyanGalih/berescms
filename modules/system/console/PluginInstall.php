<?php namespace System\Console;

use Symfony\Component\Process\Process;
use System\Classes\ComposerManager;
use System\Classes\PluginManager;
use System\Classes\UpdateManager;
use Winter\Storm\Console\Command;

/**
 * Console command to install a plugin using Composer.
 *
 * @package winter\wn-system-module
 * @author Alexey Bobkov, Samuel Georges
 */
class PluginInstall extends Command
{
    /**
     * @var string|null The default command name for lazy loading.
     */
    protected static $defaultName = 'plugin:install';

    /**
     * @var string The name and signature of this command.
     */
    protected $signature = 'plugin:install
        {name : The Composer package name of the plugin. <info>(eg: author/plugin-name)</info>}
        {--m|no-migrate : Do not automatically run database migrations after installation.}';

    /**
     * @var string The console command description.
     */
    protected $description = 'Installs a plugin via Composer and runs database migrations.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $packageName = trim((string) $this->argument('name'));

        // Validate package name (vendor/package)
        if (!preg_match('/^[a-z0-9_.-]+\/[a-z0-9_.-]+$/i', $packageName)) {
            $this->error(sprintf(
                'Invalid package name "%s". Please specify a valid Composer package name in "vendor/package" format.',
                $packageName
            ));
            return 1;
        }

        $composerBin = $this->getComposerBinary();
        $this->info(sprintf('Installing plugin package %s via Composer...', $packageName));

        $process = new Process([$composerBin, 'require', $packageName], base_path());
        $process->setTimeout(300);

        $exitCode = $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if ($exitCode !== 0) {
            $this->error(sprintf('Composer failed to install package "%s".', $packageName));
            return $exitCode;
        }

        $this->info('Package installed successfully.');

        // Refresh the root Composer loader so newly installed packages are discoverable
        ComposerManager::instance()->autoload(base_path('vendor'));

        // Ensure newly installed autoload.files entries are executed before plugin discovery
        $autoloadFiles = base_path('vendor/composer/autoload_files.php');
        if (file_exists($autoloadFiles)) {
            $included = array_flip(get_included_files());
            $files = require $autoloadFiles;
            foreach ($files as $file) {
                $real = realpath($file);
                if ($real && !isset($included[$real])) {
                    include_once $real;
                }
            }
        }

        // Reload plugins in case new plugin was added
        $pluginManager = PluginManager::instance();
        $existingPlugins = array_keys($pluginManager->getAllPlugins());
        $pluginManager->loadPlugins();

        // Register any newly discovered plugins before running migrations
        foreach ($pluginManager->getAllPlugins() as $code => $plugin) {
            if (!in_array($code, $existingPlugins, true)) {
                $pluginManager->registerPlugin($plugin, $code);
            }
        }

        // Run migrations unless --no-migrate flag is passed
        if (!$this->option('no-migrate')) {
            $this->info('Running plugin migrations...');
            UpdateManager::instance()->setNotesOutput($this->output)->update();
        }

        $this->info(sprintf('Plugin %s installed successfully.', $packageName));
        return 0;
    }

    /**
     * Get the Composer executable binary.
     */
    protected function getComposerBinary(): string
    {
        if ($customBin = env('COMPOSER_BINARY')) {
            return (string) $customBin;
        }

        return 'composer';
    }
}
