<?php

namespace App\Http\Controllers\Api\V1\Account;

use App\Http\Controllers\Controller;
use App\Models\Auth\NotificationPreference;
use App\Models\Auth\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class AccountPreferencesController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->profile()->firstOrCreate([]);
        $notifications = $user->notificationPreferences()->firstOrCreate([]);

        return response()->json([
            'data' => [
                'size_profile' => $profile->size_profile ?? [],
                'notifications' => [
                    'push_enabled' => (bool) $notifications->push_enabled,
                    'email_enabled' => (bool) $notifications->email_enabled,
                    'sms_enabled' => (bool) $notifications->sms_enabled,
                    'marketing_enabled' => (bool) $notifications->marketing_enabled,
                    'restock_enabled' => (bool) $notifications->restock_enabled,
                    'price_drop_enabled' => (bool) $notifications->price_drop_enabled,
                ],
            ],
        ]);
    }

    public function updateNotifications(Request $request): JsonResponse
    {
        $data = $request->validate([
            'push_enabled' => ['required', 'boolean'],
            'email_enabled' => ['required', 'boolean'],
            'sms_enabled' => ['required', 'boolean'],
            'marketing_enabled' => ['required', 'boolean'],
            'restock_enabled' => ['required', 'boolean'],
            'price_drop_enabled' => ['required', 'boolean'],
        ]);

        $preferences = $request->user()->notificationPreferences()->updateOrCreate([], $data);

        return response()->json(['data' => [
            'push_enabled' => (bool) $preferences->push_enabled,
            'email_enabled' => (bool) $preferences->email_enabled,
            'sms_enabled' => (bool) $preferences->sms_enabled,
            'marketing_enabled' => (bool) $preferences->marketing_enabled,
            'restock_enabled' => (bool) $preferences->restock_enabled,
            'price_drop_enabled' => (bool) $preferences->price_drop_enabled,
        ]]);
    }

    public function updateSizeProfile(Request $request): JsonResponse
    {
        $data = $request->validate([
            'top' => ['nullable', 'string', Rule::in(['XS', 'S', 'M', 'L', 'XL', 'XXL'])],
            'bottom' => ['nullable', 'string', Rule::in(['28', '30', '32', '34', '36', '38'])],
            'shoe' => ['nullable', 'string', Rule::in(['39', '40', '41', '42', '43', '44', '45'])],
        ]);

        $profile = $request->user()->profile()->updateOrCreate([], [
            'size_profile' => array_filter($data, static fn ($value) => $value !== null && $value !== ''),
        ]);

        return response()->json(['data' => $profile->size_profile ?? []]);
    }
}
