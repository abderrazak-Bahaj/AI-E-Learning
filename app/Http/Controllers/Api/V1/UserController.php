<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\UpdatePasswordRequest;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

final class UserController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $search = $request->string('search')->toString();

        $users = User::query()
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->role))
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(20);

        return $this->success(UserResource::collection($users));
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);
        $user->load($user->role);

        return $this->success(new UserResource($user));
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $request->user()->update($request->validated());

        return $this->success(
            new UserResource($request->user()->fresh()),
            'Profile updated successfully'
        );
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return $this->success(message: 'Password updated successfully');
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);
        $user->delete();

        return $this->noContent();
    }
}
