<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

abstract class Controller
{
    /**
     * Go to the list of a section, or to the dashboard when the person may not see that list.
     */
    protected function toIndex(string $resource): RedirectResponse
    {
        return Gate::allows("$resource.view")
            ? to_route("admin.$resource.index")
            : to_route('dashboard');
    }
}
