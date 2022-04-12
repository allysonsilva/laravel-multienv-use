<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

class EnvsTest extends DuskTestCase
{
    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass(): void
    {
        static::clearCacheFiles();
    }

    /**
     * @test
     * @testdox By default the variables from the last `.env` file in the root folder of the application will override / precede all previous ones.
     */
    public function using_variables_from_last_env_file(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertJsonResponse('envC');
        });
    }

    /**
     * @test
     * @testdox Using the `sorted` key to sort the precedence of `.env` files. In this case, the `.envB` file will take precedence over all others.
     */
    public function using_envB_file_in_precedent_way(): void
    {
        config()->set('envs.sorted', [
            '.envC',
            '.envA',
            '.envB',
        ]);

        $this->updateEnvsConfigFile();

        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertJsonResponse('envB');
        });
    }

    /**
     * @test
     * @testdox Using the `sorted` key to sort the precedence of `.env` files. In this case, the `.envA` file will take precedence over all others.
     */
    public function using_envA_file_in_precedent_way(): void
    {
        config()->set('envs.sorted', [
            '.envB',
            '.envC',
            '.envA',
        ]);

        $this->updateEnvsConfigFile();

        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertJsonResponse('envA');
        });
    }

    /**
     * @test
     * @testdox When there is a domain match as the filename `.env` then it should replace all others.
     */
    public function using_domain_env_file_in_precedent_way(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('http://site1.test:8010/')
                    ->assertJsonResponse('env.site1');
        });
    }

    /**
     * @test
     * @testdox Using the `domains.env` key to specify the custom name for the domain's `.env` file.
     */
    public function using_custom_name_from_domain_env_file(): void
    {
        $target = $this->envsFolder . '/.env.site2.test';
        $customFile = '.env.site2-custom.test';
        $link = $this->envsFolder . "/{$customFile}";

        @unlink($link);
        symlink($target, $link);

        config()->set('envs.domains', [
            'site2.test' => [
                'env' => $customFile,
            ]
        ]);

        $this->updateEnvsConfigFile();

        $this->browse(function (Browser $browser) use ($customFile) {
            $browser->visit('http://site2.test:8020/domain-filename')
                    ->assertSee($customFile);

            $browser->visit('http://site2.test:8020/')
                    ->assertJsonResponse('env.site2');
        });

        @unlink($link);
    }
}
