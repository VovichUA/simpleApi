<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Requests\UpdateSettingsRequest;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    /**
     * Update the user's settings.
     *
     * @param UpdateSettingsRequest $request
     * @return JsonResponse
     */
    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $user = Auth::user();
        $user->setLanguage($request->get('language'));
        $user->setTimezone($request->get('timezone'));
        $user->save();

        return response()->json(['message' => 'Settings updated successfully.'], 200);
    }
}
