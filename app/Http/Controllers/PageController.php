<?php

namespace App\Http\Controllers;

use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    /**
     * @param string $page
     *
     * @return Application|Factory|View
     */
    public function show(string $page): View|Factory|Application
    {
        // lower and disallow ../ and weird stuff.
        $page = mb_strtolower($page);
        $page = preg_replace("/[^a-z-]/", "", $page);
        $page_title = ucwords(str_replace("-", " ", $page));

        $page_content = '';
        $page = __DIR__ . '/../../../resources/markdown/' . $page . '.md';
        if (file_exists($page)) {
            $page_content = Markdown::convertToHtml(file_get_contents($page));
        }



        return view('page', [
            'page_title' => $page_title,
            'page_content' => $page_content
        ]);
    }
}