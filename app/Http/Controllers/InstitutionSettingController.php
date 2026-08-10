<?php

namespace App\Http\Controllers;

use App\Models\InstitutionSetting;
use Illuminate\Http\Request;

class InstitutionSettingController extends Controller
{
    public function show()
    {
        $institution = auth()->user()->institution;

        return $institution->settings ?? InstitutionSetting::make([
            'institution_id' => $institution->id,
            'has_departments' => false,
            'consecutive_period_blocking' => true,
        ]);
    }

    public function update(Request $request)
    {
        if (auth()->user()->role !== 'admin' && !auth()->user()->isSuperAdmin()) {
            abort(403, 'শুধু institution admin সেটিংস পরিবর্তন করতে পারবেন।');
        }

        $validated = $request->validate([
            'has_departments' => ['sometimes', 'boolean'],
            'consecutive_period_blocking' => ['sometimes', 'boolean'],
        ]);

        $institutionId = auth()->user()->institution_id;

        $settings = InstitutionSetting::updateOrCreate(
            ['institution_id' => $institutionId],
            $validated
        );

        return response()->json($settings);
    }
}
