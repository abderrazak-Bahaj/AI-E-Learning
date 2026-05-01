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
    /**
     * List all users.
     *
     * Admin only. Supports search by name/email and filter by role/status.
     */
    #[\Dedoc\Scramble\Attributes\QueryParameter('search', description: 'Search by name or email.', type: 'string', example: 'alice')]
    #[\Dedoc\Scramble\Attributes\QueryParameter('role', description: 'Filter by role: admin, teacher, student.', type: 'string', example: 'teacher')]
    #[\Dedoc\Scramble\Attributes\QueryParameter('filter[status]', description: 'Filter by status: ACTIVE, INACTIVE, SUSPENDED.', type: 'string')]
    #[\Dedoc\Scramble\Attributes\QueryParameter('per_page', description: 'Items per page (max 100).', type: 'integer', default: 20)]
    #[\Dedoc\Scramble\Attributes\QueryParameter('page', description: 'Page number.', type: 'integer', default: 1)]
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

    /**
     * Get a single user with their role profile (admin/teacher/student).
     */
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
    /**
     * Update a user's details.
     *
     * Admin only. Use `role` field to change the user's Spatie role.
     */
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
    /**
     * Change a user's account status.
     *
     * Admin only. Valid values: ACTIVE, INACTIVE, SUSPENDED.
     */
    public function updateStatus(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $request->validate([
            'status' => ['required', Rule::in(['ACTIVE', 'INACTIVE', 'SUSPENDED'])],
        ]);

        $user->update(['status' => $request->status]);

        return $this->success(new UserResource($user->fresh()), 'User status updated');
    }

    /**
     * Update the authenticated user's own profile.
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $request->user()->update($request->validated());

        return $this->success(
            new UserResource($request->user()->fresh()),
            'Profile updated successfully'
        );
    }

    /**
     * Change the authenticated user's password.
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return $this->success(message: 'Password updated successfully');
    }

    /**
     * Delete a user (soft delete).
     *
     * Admin only. Cannot delete yourself.
     */
    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);
        $user->delete();

        return $this->noContent();
    }
}
