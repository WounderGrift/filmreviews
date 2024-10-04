<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SkeletonLoader extends Component
{
    public int $style;

    public function __construct($style = null)
    {
        $this->style = $style;
    }

    public function render(): View|Closure|string
    {
        return view('components.skeleton-loader');
    }
}
