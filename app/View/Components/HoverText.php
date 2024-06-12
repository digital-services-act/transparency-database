<?php

namespace App\View\Components;

use Illuminate\View\Component;

class HoverText extends Component
{
    public $hoverText;

    public function __construct($hoverText)
    {
        $this->hoverText = $hoverText;
    }

    public function render()
    {
        return view('components.hover-text');
    }
}
