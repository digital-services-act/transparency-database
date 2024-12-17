<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Blade;
use Parsedown;

class PageController extends Controller
{
    public function show(string $page, bool $profile = false): \Illuminate\Foundation\Application|View|Factory|Redirector|Application|RedirectResponse
    {
        // lower and disallow ../ and weird stuff.
        $page = (string)mb_strtolower($page);



        // sanitize
        $page = preg_replace("/[^a-z-]/", "", $page);

//        dd($page);

        $redirects = [
            'cookie-policy'  => 'https://commission.europa.eu/cookies-policy_en',
            'latest-updates' => '/',
//            'faq' => 'faq'
        ];

        if (isset($redirects[$page])) {
            return redirect($redirects[$page]);
        }

        $page_title = ucwords(str_replace("-", " ", (string)$page));

        $page_title_mods = [
            'Api Documentation'        => 'API and Schema',
            'Api Documentation Two'  => "Api Documentation V2",
            'Onboarding Documentation' => 'Platform Onboarding Documentation',
            'Legal Information'        => 'Legal Notice',
            'Documentation'            => 'Overview Documentation',
            'Webform Documentation'    => "Webform Documentation",
            'Accessibility Statement'  => "Accessibility Statement",
            'Migration To Api V Two'  => "Migration To API v2",


        ];


        if (isset($page_title_mods[$page_title])) {
            $page_title = $page_title_mods[$page_title];
        }

        $breadcrumb = ucwords(str_replace("-", " ", (string)$page));

        $breadcrumb_mods = [
            'Home'                     => '',
            'Onboarding Documentation' => 'Onboarding Documentation',
            'Api Documentation'        => 'API and Schema',
            'Api Documentation Two'  => "Api Documentation V2",
            'Documentation'            => 'Documentation',
            'Webform Documentation'    => "Webform Documentation",
            'Legal Information'        => 'Legal Notice',
            'Accessibility Statement'  => "Accessibility Statement",
            'Migration To Api V Two'  => "Migration To API v2",
        ];

        if (isset($breadcrumb_mods[$breadcrumb])) {
            $breadcrumb = $breadcrumb_mods[$breadcrumb];
        }

        $page_content = '';
        $page         = __DIR__ . '/../../../resources/markdown/' . $page . '.md';

        $view_data = [
            'profile'            => $profile,
            'page_title'         => $page_title,
            'breadcrumb'         => $breadcrumb,
            'baseurl'            => route('home'),
        ];


        if (file_exists($page)) {
            $page_content = $this->convertMdFile($page);
            // This way blade stuff in the markdown also works.
            $page_content = Blade::render($page_content, $view_data);
        } else {
            abort(404);
        }

        $view_data['page_content'] = $page_content;

        return view('page', $view_data);
    }


    public function profileShow(string $page): Factory|View|Application
    {
        return $this->show($page, true);
    }


    private function convertMdFile(string $file): string
    {
        $parsedown = new Parsedown();

        return preg_replace_callback('/(\<h[1-6](.*?))\>(.*)(<\/h[1-6]>)/i', static function ($matches) {
            if ( ! stripos((string)$matches[0], 'id=')) {
                $id         = strtolower(str_replace(" ", "-", (string)$matches[3]));
                $matches[0] = $matches[1] . $matches[2] . ' id="' . $id . '">' . $matches[3] . $matches[4];
            }

            return $matches[0];
        }, $parsedown->text(file_get_contents($file)));
    }
}
