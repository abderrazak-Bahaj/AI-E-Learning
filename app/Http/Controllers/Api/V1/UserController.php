<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\UpdatePasswordRequest;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Requests\Api\V1\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

final class UserController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $search = $request->string('search')->toString();

        $query = User::query()
            ->when($request->filled('role'), fn ($q) => $q->role($request->role))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($search !== '', fn ($q) => $q->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->latest();

        return $this->success(UserResource::collection(
            $query->paginate((int) $request->input('per_page', 20))
        ));
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);
        $roleName = $user->getRoleNames()->first();
        if ($roleName) {
            $user->load($roleName);
        }

        return $this->success(new UserResource($user));
    }

    /** Admin: update any user's details */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $data = $request->validated();

        // Role is managed by Spatie — handle separately, not via mass assignment
        if (isset($data['role'])) {
            $user->syncRoles([$data['role']]);
            unset($data['role']);
        }

        $user->update($data);

        return $this->success(
            new UserResource($user->fresh()->load($user->getRoleNames()->first() ?? 'student')),
            'User updated successfully'
        );
    }

    /** Admin: change a user's account status */
    public function updateStatus(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $request->validate([
            'status' => ['required', Rule::in(['ACTIVE', 'INACTIVE', 'SUSPENDED'])],
        ]);

        $user->update(['status' => $request->status]);

        return $this->success(new UserResource($user->fresh()), 'User status updated');
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
