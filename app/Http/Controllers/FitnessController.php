<?php

namespace App\Http\Controllers;

use App\Models\FitnessCustomSchedule;
use App\Models\FitnessLog;
use App\Models\FitnessProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class FitnessController extends Controller
{
    public function landing(): Response
    {
        return Inertia::render('fitness/Landing');
    }

    public function onboarding(): Response|RedirectResponse
    {
        if (Auth::user()->fitnessProfile) {
            return redirect()->route('fitness.dashboard');
        }
        return Inertia::render('fitness/Onboarding');
    }

    public function storeProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'age'            => 'required|integer|min:13|max:100',
            'gender'         => 'required|in:male,female',
            'height_cm'      => 'required|numeric|min:100|max:250',
            'weight_kg'      => 'required|numeric|min:30|max:300',
            'activity_level' => 'required|in:sedentary,lightly_active,moderately_active,very_active,extra_active',
            'goal'           => 'required|in:lose_weight,maintain,gain_muscle',
            'program_type'   => 'required|in:PPL,upper_lower',
        ]);

        $bmi = FitnessProfile::calculateBmi($data['weight_kg'], $data['height_cm']);
        $calories = FitnessProfile::calculateCalorieTarget(
            $data['weight_kg'], $data['height_cm'], $data['age'],
            $data['gender'], $data['activity_level'], $data['goal'],
        );

        Auth::user()->fitnessProfile()->updateOrCreate(
            ['user_id' => Auth::id()],
            [...$data, 'bmi' => $bmi, 'calorie_target' => $calories],
        );

        return redirect()->route('fitness.dashboard');
    }

    public function dashboard(): Response|RedirectResponse
    {
        $user    = Auth::user();
        $profile = $user->fitnessProfile;

        if (! $profile) {
            return redirect()->route('fitness.onboarding');
        }

        $logs = FitnessLog::where('user_id', $user->id)
            ->orderByDesc('log_date')
            ->limit(14)
            ->get(['log_date', 'calories_consumed', 'weight_kg', 'workout_completed', 'notes']);

        $todayLog = FitnessLog::where('user_id', $user->id)
            ->where('log_date', today())
            ->first();

        return Inertia::render('fitness/Dashboard', [
            'profile'  => $profile,
            'logs'     => $logs,
            'todayLog' => $todayLog,
            'userName' => $user->name,
        ]);
    }

    public function schedule(): Response|RedirectResponse
    {
        $user    = Auth::user();
        $profile = $user->fitnessProfile;

        if (! $profile) {
            return redirect()->route('fitness.onboarding');
        }

        $custom = FitnessCustomSchedule::where('user_id', $user->id)
            ->where('program_type', $profile->program_type)
            ->first();

        return Inertia::render('fitness/Schedule', [
            'programType'    => $profile->program_type,
            'goal'           => $profile->goal,
            'customSchedule' => $custom?->schedule_data,
        ]);
    }

    public function saveSchedule(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'schedule_data'                        => 'required|array|min:1',
            'schedule_data.*.label'                => 'required|string|max:20',
            'schedule_data.*.tag'                  => 'required|string|max:30',
            'schedule_data.*.rest'                 => 'required|boolean',
            'schedule_data.*.exercises'            => 'present|array',
            'schedule_data.*.exercises.*.name'     => 'required|string|max:80',
            'schedule_data.*.exercises.*.sets'     => 'required|integer|min:1|max:10',
            'schedule_data.*.exercises.*.reps'     => 'required|string|max:20',
            'schedule_data.*.exercises.*.muscle'   => 'required|string|max:40',
        ]);

        $profile = Auth::user()->fitnessProfile;

        FitnessCustomSchedule::updateOrCreate(
            ['user_id' => Auth::id(), 'program_type' => $profile->program_type],
            ['schedule_data' => $data['schedule_data']],
        );

        return back()->with('success', 'Schedule saved!');
    }

    public function resetSchedule(): RedirectResponse
    {
        $profile = Auth::user()->fitnessProfile;

        FitnessCustomSchedule::where('user_id', Auth::id())
            ->where('program_type', $profile->program_type)
            ->delete();

        return back()->with('success', 'Schedule reset to default.');
    }

    public function storeLog(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'calories_consumed'  => 'nullable|integer|min:0|max:9999',
            'weight_kg'          => 'nullable|numeric|min:30|max:300',
            'workout_completed'  => 'boolean',
            'notes'              => 'nullable|string|max:500',
        ]);

        FitnessLog::updateOrCreate(
            ['user_id' => Auth::id(), 'log_date' => today()],
            $data,
        );

        return back();
    }
}
