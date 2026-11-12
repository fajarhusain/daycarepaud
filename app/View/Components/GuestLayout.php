<?php

namespace App\View\Components;

use App\Support\GuestCms;
use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    public function __construct(
        public string $maxWidth = 'max-w-md',
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.guest', [
            'maxWidth' => $this->maxWidth,
            'cms' => GuestCms::data(),
        ]);
    }
}
