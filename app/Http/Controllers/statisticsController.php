<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class statisticsController extends Controller
{
    private const VALID_ACTIVITY_TYPES = [
        'Telefonát klient',
        'Prvé stretnutie',
        'Analýza osobných financí',
        'poradenstvo',
        'realizácia'
    ];

    private const ACTIVITY_TYPE_MAPPING = [
        'telefonat' => 'Telefonát klient',
        'klient' => 'Prvé stretnutie',
        'aof' => 'Analýza osobných financí',
        'poradenstvo' => 'poradenstvo',
        'realizacia' => 'realizácia'
    ];

    public function getStatistics(Request $request)
    {
        try {
            $request->validate([
                'from_date' => 'required|date',
                'to_date' => 'required|date',
                'activity_type' => 'string|nullable'
            ]);

            // Get current authenticated user
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Debug logging
            Log::info('Statistics request', [
                'user_id' => $user->id,
                'from_date' => $request->from_date,
                'to_date' => $request->to_date,
                'activity_type' => $request->activity_type
            ]);

            $query = Activity::query()
                ->whereBetween('datumCas', [$request->from_date, $request->to_date])
                ->where('created_id', $user->id);

            // Handle activity type filtering
            if ($request->activity_type && $request->activity_type !== 'all') {
                $query->where('aktivita', $request->activity_type);
            } else {
                $query->whereIn('aktivita', self::VALID_ACTIVITY_TYPES);
            }

            // Get all activities within the date range
            $activities = $query->with('contact')->get();

            // Debug logging
            Log::info('Found activities', ['count' => $activities->count()]);

            // Group activities by contact_id and get the latest activity for each contact
            $latestActivities = $activities->groupBy('contact_id')->map(function ($contactActivities) {
                return $contactActivities->sortByDesc('datumCas')->first();
            });

            // Initialize statistics
            $statistics = [
                'called' => 0,
                'reached' => 0,
                'scheduled' => 0
            ];

            // Calculate statistics based on the latest activity for each contact
            foreach ($latestActivities as $activity) {
                if (!is_null($activity->volane)) $statistics['called']++;
                if (!is_null($activity->dovolane)) $statistics['reached']++;
                if (!is_null($activity->dohodnute)) $statistics['scheduled']++;
            }

            // Prepare table data
            $tableData = $activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'meno' => $activity->contact->meno ?? '',
                    'priezvisko' => $activity->contact->priezvisko ?? '',
                    'type' => $activity->aktivita,
                    'date' => $activity->datumCas,
                    'status' => $this->getActivityStatus($activity)
                ];
            });

            return response()->json([
                'statistics' => $statistics,
                'activities' => $tableData
            ]);

        } catch (\Exception $e) {
            Log::error('Statistics error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getActivityStatus($activity)
    {
        if ($activity->aktivita === 'Telefonát klient') {
            if (!is_null($activity->dohodnute)) return 'dohodnuté';
            if (!is_null($activity->dovolane)) return 'dovolané';
            if (!is_null($activity->volane)) return 'volané';
        }
        return !is_null($activity->dohodnute) ? 'dohodnuté' : 'nezrealizované';
    }
}