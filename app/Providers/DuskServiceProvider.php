<?php

namespace App\Providers;

use Laravel\Dusk\Browser;
use Illuminate\Support\ServiceProvider;
use PHPUnit\Framework\Assert as PHPUnit;

class DuskServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Browser::macro('assertJsonResponse', function (string $fixture) {
            $body = $this->resolver->findOrFail('');

            $fixturePath = realpath(__DIR__ . '/../../tests/Browser/fixtures');

            $fixtureFile = file_get_contents($fixturePath . "/{$fixture}.json");
            $fixture = json_encode(json_decode($fixtureFile, true), 15);

            PHPUnit::assertSame($fixture, $body->getText());

            return $this;
        });

        Browser::macro('assertJsonCached', function (array $expected, string $url) {
            $this->visit($url);

            $bodyText = $this->resolver->findOrFail('')->getText();

            $expectedJson = json_encode($expected, 15);

            PHPUnit::assertJsonStringEqualsJsonString($expectedJson, $bodyText);
        });

        Browser::macro('assertJsonRoutesCached', function (string $url, string $cachedRouteFilename) {
            $expected = [
                'routesAreCached' => true,
                'getCachedRoutesPath' => base_path('bootstrap/cache/' . $cachedRouteFilename),
            ];

            $this->assertJsonCached($expected, $url);

            return $this;
        });

        Browser::macro('assertJsonConfigsCached', function (string $url, string $cachedConfigFilename) {
            $expected = [
                'configurationIsCached' => true,
                'getCachedConfigPath' => base_path('bootstrap/cache/' . $cachedConfigFilename),
            ];

            $this->assertJsonCached($expected, $url);

            return $this;
        });
    }
}
