<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;


class Locale
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $browserLocale = $this->getBrowserLocale();
        if (session("force_lang") && $browserLocale == session('locale')){
            session(['force_lang' => false]);
        }
        if (session("force_lang")) {
            $request->lang = session('locale');
        }

        if (isset($request->lang)) {
            $lang = strtolower($request->lang);
            if (in_array($lang, config('app.locales'))) {
                session(['locale' => $lang]);
            }
        } else{
            $browserLocale = $this->getBrowserLocale();
            session(['locale' => $browserLocale]);
        }

        $raw_locale = session('locale');
        //dd(Config::get('app.locales'));
        if (in_array($raw_locale, Config::get('app.locales'))) {
            $locale = $raw_locale;
        } else $locale = Config::get('app.locale');
        App::setLocale($locale);

        return $next($request);
    }

    function getBrowserLocale()
    {

        $websiteLanguages = config('app.locales');

        if(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
            $http_accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        } else {
//            Log::info("No http accept language detected. Using the default one");
            $http_accept_language = "en-US,en;q=0.9,fr;q=0.8";
        }



        preg_match_all(
            '/([a-z]{1,8})' .       // M1 - First part of language e.g en
            '(-[a-z]{1,8})*\s*' .   // M2 -other parts of language e.g -us
            // Optional quality factor M3 ;q=, M4 - Quality Factor
            '(;\s*q\s*=\s*((1(\.0{0,3}))|(0(\.[0-9]{0,3}))))?/i',
            $http_accept_language,
            $langParse);

        $langs = $langParse[1]; // M1 - First part of language
        $quals = $langParse[4]; // M4 - Quality Factor

        $numLanguages = count($langs);
        $langArr = array();

        for ($num = 0; $num < $numLanguages; $num++)
        {
            $newLang = $langs[$num];
            $newQual = isset($quals[$num]) ?
                (empty($quals[$num]) ? 1.0 : floatval($quals[$num])) : 0.0;

            // Choose whether to upgrade or set the quality factor for the
            // primary language.
            $langArr[$newLang] = (isset($langArr[$newLang])) ?
                max($langArr[$newLang], $newQual) : $newQual;
        }

        // sort list based on value
        // langArr will now be an array like: array('EN' => 1, 'ES' => 0.5)
        arsort($langArr, SORT_NUMERIC);

        // The languages the client accepts in order of preference.
        $acceptedLanguages = array_keys($langArr);

        // Set the most preferred language that we have a translation for.
        foreach ($acceptedLanguages as $preferredLanguage)
        {
            if (in_array($preferredLanguage, $websiteLanguages))
            {
                $_SESSION['lang'] = $preferredLanguage;
                return strtolower($preferredLanguage);
            }
        }
    }
}