<?php

namespace App\Http\Controllers;

use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Activity::class);

        $activities = Activity::with('causer')
            ->latest()
            ->paginate(20);

        return view('activity-log.index', compact('activities'));
    }
}
