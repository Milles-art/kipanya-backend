<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Facades\RateLimiter;

final class ContactController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:160'],
            'type' => ['required', 'string', 'max:60'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        // Honeypot: the field is invisible to people, so anything in it is a bot. Answer
        // exactly like a success so the bot learns nothing, and store nothing.
        if ($request->filled('website')) {
            return response()->json([
                'message' => 'Your message has been sent. We will get back to you as soon as possible.',
                'data' => ['id' => 0],
            ], 201);
        }

        // Per-sender cap on top of the per-IP route throttle (bots rotate IPs, not inboxes).
        $senderKey = 'contact:'.sha1(mb_strtolower($data['email']));
        if (! RateLimiter::attempt($senderKey, 3, static fn () => true, 3600)) {
            throw new ThrottleRequestsException('You have sent several messages recently. Please wait before sending another.');
        }

        $message = ContactMessage::create($data + ['status' => 'new']);

        return response()->json([
            'message' => 'Your message has been sent. We will get back to you as soon as possible.',
            'data' => ['id' => $message->id],
        ], 201);
    }
}
