<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\Content\ContentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\EpisodeRequest;
use App\Models\Content\Cartoon;
use App\Models\Content\CartoonEpisode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;
use App\Support\AuditLogger;

final class EpisodeController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function index(Cartoon $cartoon)
    {
        return JsonResource::collection($cartoon->episodes()->paginate(30));
    }

    public function store(EpisodeRequest $request, Cartoon $cartoon): JsonResponse
    {
        $data = $request->validated();
        if (($data['status'] ?? ContentStatus::Draft->value) === ContentStatus::Published->value) $data['published_at'] ??= now();
        $episode = $cartoon->episodes()->create($data);
        $this->auditLogger->log($request, 'admin.episode.created', $episode, ['cartoon_id' => $cartoon->getKey()]);
        return response()->json(['data' => $episode], 201);
    }

    public function show(Cartoon $cartoon, CartoonEpisode $episode): JsonResponse
    {
        abort_unless($episode->cartoon_id === $cartoon->id, 404);
        return response()->json(['data' => $episode]);
    }

    public function update(EpisodeRequest $request, Cartoon $cartoon, CartoonEpisode $episode): JsonResponse
    {
        abort_unless($episode->cartoon_id === $cartoon->id, 404);
        $data = $request->validated();
        if (($data['status'] ?? $episode->status->value) === ContentStatus::Published->value) $data['published_at'] ??= $episode->published_at ?? now();
        $episode->update($data);
        $this->auditLogger->log($request, 'admin.episode.updated', $episode, ['cartoon_id' => $cartoon->getKey()]);
        return response()->json(['data' => $episode->fresh()]);
    }

    public function destroy(Request $request, Cartoon $cartoon, CartoonEpisode $episode): JsonResponse
    {
        abort_unless($episode->cartoon_id === $cartoon->id, 404);
        $episode->delete();
        $this->auditLogger->log($request, 'admin.episode.deleted', $episode, ['cartoon_id' => $cartoon->getKey(), 'episode_id' => $episode->getKey()]);
        return response()->json(['message' => 'Episode deleted.']);
    }
}
