<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PinReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function show(Request $request, User $user): JsonResponse
    {
        $canViewPrivateProfile = $request->user()->is($user)
            || $request->user()->following()->whereKey($user->id)->exists();
        $isPrivate = $user->private_account && ! $canViewPrivateProfile;

        $user->loadCount(['followers', 'following', 'pins']);

        $pins = $isPrivate
            ? collect()
            : $user->pins()
                ->with('user:id,name,username,avatar_url')
                ->withExists([
                    'savedBy as is_saved' => fn (Builder $query) => $query->whereKey($request->user()->id),
                ])
                ->latest('id')
                ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $this->publicProfile($user),
                'is_current_user' => $request->user()->is($user),
                'is_following' => $request->user()->following()->whereKey($user->id)->exists(),
                'is_private' => $isPrivate,
                'pins' => $pins,
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->merge([
            'username' => Str::lower(trim((string) $request->input('username'))),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'alpha_dash',
                'max:30',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'bio' => ['nullable', 'string', 'max:500'],
            'private_account' => ['sometimes', 'boolean'],
            'avatar' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $user->fill([
            'name' => trim($validated['name']),
            'username' => $validated['username'],
            'bio' => isset($validated['bio']) ? trim($validated['bio']) : null,
            'private_account' => (bool) ($validated['private_account'] ?? $user->private_account),
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path !== null) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar_path = $path;
            $user->avatar_url = Storage::disk('public')->url($path);
        }

        $user->save();
        $user->loadCount(['followers', 'following', 'pins']);

        return response()->json([
            'success' => true,
            'data' => $this->publicProfile($user),
        ]);
    }

    public function reports(Request $request): JsonResponse
    {
        $reports = PinReport::query()
            ->with(['pin:id,user_id,title,image_url', 'pin.user:id,name,username,avatar_url'])
            ->whereBelongsTo($request->user(), 'user')
            ->latest('id')
            ->get(['id', 'pin_id', 'reason', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => $reports,
        ]);
    }

    public function follow(Request $request, User $user): JsonResponse
    {
        if ($request->user()->is($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu tidak bisa mengikuti akun sendiri.',
            ], 422);
        }

        $request->user()->following()->syncWithoutDetaching([$user->id]);

        return response()->json([
            'success' => true,
            'is_following' => true,
        ]);
    }

    public function unfollow(Request $request, User $user): JsonResponse
    {
        $request->user()->following()->detach($user->id);

        return response()->json([
            'success' => true,
            'is_following' => false,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function publicProfile(User $user): array
    {
        return $user->only([
            'id',
            'name',
            'username',
            'bio',
            'avatar_url',
            'private_account',
            'followers_count',
            'following_count',
            'pins_count',
        ]);
    }
}

