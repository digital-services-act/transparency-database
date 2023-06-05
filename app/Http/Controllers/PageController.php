<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Blade;
use Parsedown;

class PageController extends Controller
{
    /**
     * @param string $page
     * @param string $view
     *
     * @return Application|Factory|View
     */
    public function show(string $page, string $view = 'page'): View|Factory|Application
    {
        // lower and disallow ../ and weird stuff.
        $page = mb_strtolower($page);
        $page = preg_replace("/[^a-z-]/", "", $page);
        $page_title = ucwords(str_replace("-", " ", $page));

        $page_content = '';
        $page = __DIR__ . '/../../../resources/markdown/' . $page . '.md';

        $view_data = [
            'page_title' => $page_title,
            'baseurl' => route('home'),
            'no_ecl_init' => true,
        ];

        if (file_exists($page)) {
            $page_content = $this->convertMdFile($page);
            // This way blade stuff in the markdown also works.
            $page_content = Blade::render($page_content, $view_data);
        }

        $view_data['page_content'] = $page_content;

        return view($view, $view_data);
    }

    public function dashboardShow(string $page): Factory|View|Application
    {
        return $this->show($page, 'dashboard-page');
    }


    private function convertMdFile(string $file): string
    {
        $parsedown = new Parsedown();
        return preg_replace_callback( '/(\<h[1-6](.*?))\>(.*)(<\/h[1-6]>)/i', function( $matches ) {
            if ( ! stripos( $matches[0], 'id=' ) ) {
                $id = strtolower(str_replace(" ", "-", $matches[3]));
                $matches[0] = $matches[1] . $matches[2] . ' id="' . $id . '">' . $matches[3] . $matches[4];
            }
            return $matches[0];
        }, $parsedown->text(file_get_contents($file)));
    }
}