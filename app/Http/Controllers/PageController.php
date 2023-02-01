<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use Parsedown;
use Spatie\CommonMarkShikiHighlighter\HighlightCodeExtension;


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
            $page_content = $this->convertMdFile($page);
        }

        return view('page', [
            'page_title' => $page_title,
            'page_content' => $page_content
        ]);
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


    private function convertToHtml(string $markdown, string $theme = 'github-dark'): string
    {
        $commonMarkConverter = new CommonMarkConverter();
        $environment = $commonMarkConverter->getEnvironment();
        $environment->addExtension(new HighlightCodeExtension($theme));
        $environment->addExtension(new CommonMarkCoreExtension());

        return $commonMarkConverter->convert($markdown);
    }
}