<?php

namespace Tests\Feature\Http\Middleware;

use App\Http\Middleware\Locale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    private Locale $middleware;

    private Request $request;

    protected bool $seed = false;

    #[\Override]
    protected function setUpFullySeededDatabase($statement_count = 10): void
    {
        // Do nothing - we don't need any seeding for this test
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Create middleware instance
        $this->middleware = new Locale;

        // Create base request
        $this->request = Request::create('/', 'GET');

        // Clear session
        Session::flush();

        // Set default locales configuration
        Config::set('app.locale', 'en');
        Config::set('app.locales', ['en', 'fr', 'de']);
    }

    /** @test */
    public function it_uses_default_locale_when_no_preferences_set()
    {
        // Remove HTTP_ACCEPT_LANGUAGE if it exists
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        }

        $response = $this->middleware->handle($this->request, function ($request) {
            return response()->noContent();
        });

        $this->assertEquals('en', App::getLocale());
        $this->assertEquals('en', session('locale'));
    }

    /** @test */
    public function it_sets_locale_from_query_parameter()
    {
        $request = Request::create('/', 'GET', ['lang' => 'fr']);

        $response = $this->middleware->handle($request, function ($request) {
            return response()->noContent();
        });

        $this->assertEquals('fr', App::getLocale());
        $this->assertEquals('fr', session('locale'));
    }

    /** @test */
    public function it_ignores_invalid_locale_in_query_parameter()
    {
        $request = Request::create('/', 'GET', ['lang' => 'invalid']);

        $response = $this->middleware->handle($request, function ($request) {
            return response()->noContent();
        });

        $this->assertEquals('en', App::getLocale());
    }

    /** @test */
    public function it_uses_browser_locale_when_available()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7';

        $response = $this->middleware->handle($this->request, function ($request) {
            return response()->noContent();
        });

        $this->assertEquals('fr', App::getLocale());
        $this->assertEquals('fr', session('locale'));
    }

    /** @test */
    public function it_handles_multiple_browser_locales_in_order_of_preference()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de-DE,de;q=0.9,fr-FR;q=0.8,fr;q=0.7,en-US;q=0.6,en;q=0.5';

        $response = $this->middleware->handle($this->request, function ($request) {
            return response()->noContent();
        });

        $this->assertEquals('de', App::getLocale());
        $this->assertEquals('de', session('locale'));
    }

    /** @test */
    public function it_maintains_forced_language_preference()
    {
        // Set a forced language preference
        session(['force_lang' => true, 'locale' => 'fr']);

        $response = $this->middleware->handle($this->request, function ($request) {
            return response()->noContent();
        });

        $this->assertEquals('fr', App::getLocale());
        $this->assertEquals('fr', session('locale'));
        $this->assertTrue(session('force_lang'));
    }

    /** @test */
    public function it_removes_force_lang_when_browser_matches_session()
    {
        // Set forced language that matches browser preference
        session(['force_lang' => true, 'locale' => 'fr']);
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7';

        $response = $this->middleware->handle($this->request, function ($request) {
            return response()->noContent();
        });

        $this->assertEquals('fr', App::getLocale());
        $this->assertEquals('fr', session('locale'));
        $this->assertFalse(session('force_lang'));
    }

    /** @test */
    public function it_uses_first_supported_locale_from_browser_preferences()
    {
        // Set browser preferences with unsupported locale first
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'es-ES,es;q=0.9,fr-FR;q=0.8,fr;q=0.7,en-US;q=0.6,en;q=0.5';

        $response = $this->middleware->handle($this->request, function ($request) {
            return response()->noContent();
        });

        $this->assertEquals('fr', App::getLocale());
        $this->assertEquals('fr', session('locale'));
    }

    /** @test */
    public function it_falls_back_to_default_locale_for_unsupported_browser_locales()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'es-ES,es;q=0.9,it-IT;q=0.8,it;q=0.7';

        $response = $this->middleware->handle($this->request, function ($request) {
            return response()->noContent();
        });

        $this->assertEquals('en', App::getLocale());
        $this->assertEquals('en', session('locale'));
    }
}
