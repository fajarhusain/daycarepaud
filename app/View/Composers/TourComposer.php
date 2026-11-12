<?php

namespace App\View\Composers;

use App\Support\TourRegistry;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class TourComposer
{
    public function compose(View $view): void
    {
        if (! auth()->check()) {
            $view->with([
                'tourCurrentRoute' => null,
                'tourSessionContext' => [],
                'tourHubRoute' => null,
                'tourPageSteps' => [],
                'tourModalSteps' => [],
            ]);

            return;
        }

        $currentRoute = Route::currentRouteName();
        $tourSessionContext = TourRegistry::contextForRoute($currentRoute);
        $tourHubRoute = $tourSessionContext['hubRoute'];

        $view->with([
            'tourCurrentRoute' => $currentRoute,
            'tourSessionContext' => $tourSessionContext,
            'tourHubRoute' => $tourHubRoute,
            'tourPageSteps' => TourRegistry::pageSteps($currentRoute),
            'tourModalSteps' => TourRegistry::modalStepsByType($tourHubRoute ?? $currentRoute),
        ]);
    }
}
