<?php

namespace Tests;

use Symfony\Component\Process\Process;
use Laravel\Dusk\TestCase as BaseTestCase;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    protected string $envsFolder;

    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $command = self::runArtisan('vendor:publish', '--tag=envs-config', '--force');

        self::assertTrue(str_contains($command->getOutput(), "To [/config/envs.php]\nPublishing complete."));
    }

    /**
     * Register the base URL with Dusk.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->envsFolder = realpath(__DIR__ . '/../envs/');

        parent::setUp();

        $command = self::runArtisan('vendor:publish', '--tag=envs-config', '--force');

        self::assertTrue(str_contains($command->getOutput(), "To [/config/envs.php]\nPublishing complete."));
    }

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
        if (! static::runningInSail()) {
            static::startChromeDriver();
        }
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $options = (new ChromeOptions)->addArguments(collect([
            '--window-size=1920,1080',
        ])->unless($this->hasHeadlessDisabled(), function ($items) {
            return $items->merge([
                '--disable-gpu',
                '--headless',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }

    /**
     * Determine whether the Dusk command has disabled headless mode.
     *
     * @return bool
     */
    protected function hasHeadlessDisabled()
    {
        return isset($_SERVER['DUSK_HEADLESS_DISABLED']) ||
               isset($_ENV['DUSK_HEADLESS_DISABLED']);
    }

    /**
     * Update the `envs.php` config file.
     *
     * @return void
     */
    protected function updateEnvsConfigFile(): void
    {
        $newEnvsConfig = '<?php return ' . var_export(config('envs'), true) . ';';

        $configPath = $this->app->configPath('envs.php');

        $fpConfig = fopen($configPath, 'w+');

        if (flock($fpConfig, LOCK_EX | LOCK_NB)) {
            fwrite($fpConfig, $newEnvsConfig);
            flock($fpConfig, LOCK_UN);
        }

        fclose($fpConfig);

        $this->clearEnvs();
    }

    /**
     * Run artisan in a php process using `proc_open`.
     *
     * @param string[] $command
     *
     * @return \Symfony\Component\Process\Process
     */
    protected static function runArtisan(string ...$command): Process
    {
        $artisan = realpath(__DIR__ . '/../artisan');

        $process = new Process(['php', $artisan, ...$command]);
        $process->run();

        return $process;
    }

    /**
     * When php execution commands like: `shell_exec` or `exec` are executed,
     * they see the environment variables set via `putenv`, i.e. from the parent process,
     * so the function below removes these variables before commands are executed.
     * So that the environment is as close as possible to a terminal execution!
     *
     * @return void
     */
    protected function clearEnvs(): void
    {
        putenv('ENV_NAME');
        putenv('APP_NAME');
        putenv('APP_ENV');
        putenv('APP_DEBUG');
        putenv('APP_URL');

        putenv('ENV_NAME_A');
        putenv('ENV_NAME_B');
        putenv('ENV_NAME_C');

        putenv('APP_CONFIG_CACHE');
        putenv('APP_ROUTES_CACHE');
        putenv('APP_EVENTS_CACHE');
    }

    /**
     * Clear cache files from bootstrap folder every test.
     *
     * @return void
     */
    protected static function clearCacheFiles(): void
    {
        $appBasePath = app()->basePath();

        exec(sprintf('find %s -maxdepth 1 -type f -name "*.php" -exec rm "{}" \;', escapeshellarg("{$appBasePath}/bootstrap/cache/")));
    }
}
