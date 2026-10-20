<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pin;
use App\Models\PinReport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PinController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $pins = Pin::query()
            ->where(fn (Builder $query) => $query
                ->whereNull('user_id')
                ->orWhereHas('user', fn (Builder $user) => $this->visibleTo($user, $request)))
            ->with('user:id,name,username,avatar_url')
            ->withExists([
                'savedBy as is_saved' => fn (Builder $query) => $query->whereKey($request->user()->id),
            ])
            ->latest('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pins,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['nullable', 'string', 'in:Gaya Hidup,Dekorasi,Resep,Fashion,Seni'],
            'link_url' => ['nullable', 'url:http,https', 'max:2048'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $title = trim((string) $validated['title']);
        $description = $validated['description'] !== null
            ? trim((string) $validated['description'])
            : null;

        $imageFile = $request->file('image');
        $fileName = Str::uuid()->toString().'.'.$imageFile->guessExtension();
        $imagePath = $imageFile->storeAs('pins', $fileName, 'public');

        $pin = Pin::create([
            'user_id' => $request->user()->id,
            'title' => $title,
            'description' => $description !== '' ? $description : null,
            'category' => $validated['category'] ?? 'Gaya Hidup',
            'link_url' => $validated['link_url'] ?? null,
            'image_url' => Storage::disk('public')->url($imagePath),
            'image_path' => $imagePath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pin berhasil dibuat',
            'data' => $pin->load('user:id,name,username,avatar_url'),
        ], 201);
    }

    public function destroy(Pin $pin): JsonResponse
    {
        Gate::authorize('delete', $pin);

        if ($pin->image_path !== null) {
            Storage::disk('public')->delete($pin->image_path);
        }

        $pin->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pin berhasil dihapus.',
        ]);
    }

    public function save(Request $request, Pin $pin): JsonResponse
    {
        $request->user()->savedPins()->syncWithoutDetaching([$pin->id]);

        return response()->json([
            'success' => true,
            'is_saved' => true,
        ]);
    }

    public function unsave(Request $request, Pin $pin): JsonResponse
    {
        $request->user()->savedPins()->detach($pin->id);

        return response()->json([
            'success' => true,
            'is_saved' => false,
        ]);
    }

    public function saved(Request $request): JsonResponse
    {
        $pins = $request->user()->savedPins()
            ->whereHas('user', fn (Builder $query) => $this->visibleTo($query, $request))
            ->with('user:id,name,username,avatar_url')
            ->withExists([
                'savedBy as is_saved' => fn (Builder $query) => $query->whereKey($request->user()->id),
            ])
            ->latest('pins.id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pins,
        ]);
    }

    public function report(Request $request, Pin $pin): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        if ($pin->user_id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu tidak bisa melaporkan pin milik sendiri.',
            ], 422);
        }

        $report = PinReport::query()->firstOrCreate(
            ['user_id' => $request->user()->id, 'pin_id' => $pin->id],
            ['reason' => trim($validated['reason'])],
        );

        return response()->json([
            'success' => true,
            'message' => $report->wasRecentlyCreated
                ? 'Laporan berhasil dikirim.'
                : 'Pin ini sudah pernah kamu laporkan.',
        ], $report->wasRecentlyCreated ? 201 : 200);
    }

    private function visibleTo(Builder $query, Request $request): Builder
    {
        return $query->where(function (Builder $visibility) use ($request): void {
            $visibility
                ->where('private_account', false)
                ->orWhereKey($request->user()->id)
                ->orWhereHas('followers', fn (Builder $followers) => $followers->whereKey($request->user()->id));
        });
    }
}
