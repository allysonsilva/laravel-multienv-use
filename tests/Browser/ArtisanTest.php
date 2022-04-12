<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

class ArtisanTest extends DuskTestCase
{
    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        static::clearCacheFiles();
    }

    /**
     * @test
     * @testdox Using `APP_ROUTES_CACHE` key from domain `envs` config to retrieve your cached routes.
     */
    public function using_cached_domain_routes(): void
    {
        $this->browse(function (Browser $browser) {
            config(['envs.domains' => [
                'site1.test' => [
                    'APP_ROUTES_CACHE' => 'routes-site1-test.php',
                ],
                'site2.test' => [
                    'APP_ROUTES_CACHE' => 'routes-site2-test.php',
                ]
            ]]);

            $this->updateEnvsConfigFile();

            $commandOutput = self::runArtisan('route:cache', '--domain=site2.test')->getOutput();

            self::assertTrue(str_contains($commandOutput, 'Route cache cleared'));
            self::assertTrue(str_contains($commandOutput, 'Routes cached successfully'));

            $cachedRoutesFilename = base_path('bootstrap/cache/routes-site2-test.php');

            self::assertTrue($this->app['files']->exists($cachedRoutesFilename));
            self::assertFalse(str_contains(file_get_contents($cachedRoutesFilename), 'site1.test'));

            $browser->assertJsonRoutesCached('http://site2.test:8020/cached-routes', 'routes-site2-test.php');

            self::runArtisan('route:clear', '--domain=site2.test');

            self::assertFalse($this->app['files']->exists($cachedRoutesFilename));
        });
    }

    /**
     * @test
     * @testdox When the `--domain` parameter is not present in the `route:cache` command, then all routes must be cached.
     */
    public function caching_all_routes(): void
    {
        $this->browse(function (Browser $browser) {
            $commandOutput = self::runArtisan('route:cache')->getOutput();

            self::assertTrue(str_contains($commandOutput, 'Route cache cleared'));
            self::assertTrue(str_contains($commandOutput, 'Routes cached successfully'));

            $cachedRoutesFilename = base_path('bootstrap/cache/routes-v7.php');
            $cachedRoutesContent = file_get_contents($cachedRoutesFilename);

            self::assertTrue($this->app['files']->exists($cachedRoutesFilename));
            self::assertTrue(str_contains($cachedRoutesContent, 'site1.test'));
            self::assertTrue(str_contains($cachedRoutesContent, 'site2.test'));

            $browser->assertJsonRoutesCached('http://site2.test:8020/cached-routes', 'routes-v7.php');
            $browser->assertJsonRoutesCached('http://site1.test:8010/cached-routes', 'routes-v7.php');

            self::runArtisan('route:clear');

            self::assertFalse($this->app['files']->exists($cachedRoutesFilename));
        });
    }

    /**
     * @test
     * @testdox Using `APP_CONFIG_CACHE` key from domain `envs` config to retrieve your cached configs.
     */
    public function using_cached_domain_configs(): void
    {
        config()->set('envs.domains', [
            'site1.test' => [
                'APP_CONFIG_CACHE' => 'config-site1-test.php',
            ],
            'site2.test' => [
                'APP_CONFIG_CACHE' => 'config-site2-test.php',
            ],
        ]);

        $this->updateEnvsConfigFile();

        $commandOutput = self::runArtisan('config:cache', '--domain=site1.test')->getOutput();

        self::assertTrue(str_contains($commandOutput, 'Configuration cache cleared'));
        self::assertTrue(str_contains($commandOutput, 'Configuration cached successfully'));

        $cachedConfigFilename = base_path('bootstrap/cache/config-site1-test.php');

        self::assertTrue($this->app['files']->exists($cachedConfigFilename));

        $this->browse(function (Browser $browser) {
            $browser->assertJsonConfigsCached('http://site1.test:8010/cached-config', 'config-site1-test.php');
        });

        $cachedConfig = require $cachedConfigFilename;

        $jsonFixtureSite1 = $this->getFixture('env.site1');

        $this->assertEquals($cachedConfig['domain'], $jsonFixtureSite1);

        self::runArtisan('config:clear', '--domain=site1.test');

        self::assertFalse($this->app['files']->exists($cachedConfigFilename));
    }

    /**
     * @test
     * @testdox When the `--domain` parameter is not present in the `config:cache` command, then all configs must be cached.
     */
    public function caching_all_configs(): void
    {
        $this->clearEnvs();
        $commandOutput = self::runArtisan('config:cache')->getOutput();

        self::assertTrue(str_contains($commandOutput, 'Configuration cache cleared'));
        self::assertTrue(str_contains($commandOutput, 'Configuration cached successfully'));

        $cachedConfigFilename = base_path('bootstrap/cache/config.php');

        self::assertTrue($this->app['files']->exists($cachedConfigFilename));

         $this->browse(function (Browser $browser) {
            $browser->assertJsonConfigsCached('http://site1.test:8010/cached-config', 'config.php');
        });

        $cachedConfig = require $cachedConfigFilename;

        self::assertArrayHasKey('app', $cachedConfig);
        self::assertArrayHasKey('auth', $cachedConfig);
        self::assertArrayHasKey('domain', $cachedConfig);
        self::assertArrayHasKey('database', $cachedConfig);

        $this->assertEquals($cachedConfig['domain'], $this->getFixture('envC'));

        self::runArtisan('config:clear');
        self::assertFalse($this->app['files']->exists($cachedConfigFilename));
    }

    /**
     * Retrieves the fixture of the `$name` parameter.
     *
     * @param string $name
     *
     * @return array
     */
    protected function getFixture(string $name): array
    {
        $jsonContent = file_get_contents(__DIR__ . "/fixtures/{$name}.json");

        return json_decode($jsonContent, true);
    }
}
