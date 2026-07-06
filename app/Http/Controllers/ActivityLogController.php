<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with(['user', 'subject']);

        if ($request->filled('search')) {
            $search = $request->query('search');

            $query->where(function ($query) use ($search) {
                $query->where('action', 'like', "%{$search}%")
                    ->orWhere('subject_type', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%");
            });
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->query('user_id'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->query('action'));
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->query('subject_type'));
        }

        $activities = $query->latest()->paginate(25)->withQueryString();
        $users = User::orderBy('name')->get();
        $subjects = ActivityLog::select('subject_type')->distinct()->orderBy('subject_type')->pluck('subject_type');

        return view('activity_logs.index', compact('activities', 'users', 'subjects'));
    }
}
