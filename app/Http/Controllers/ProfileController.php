<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\AuditLog;
use App\Support\ImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
            'recentActivity' => AuditLog::query()
                ->where('user_id', $request->user()->id)
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $previousAvatar = $user->avatar;
        $newAvatar = null;

        $user->fill($request->safe()->except('avatar'));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->hasFile('avatar')) {
            try {
                $newAvatar = ImageStorage::storeOptimized($request->file('avatar'), 'avatars', 640, 80);
            } catch (\Throwable $exception) {
                report($exception);

                throw ValidationException::withMessages([
                    'avatar' => 'No se pudo guardar la foto en esta computadora. Verifica que storage/app/public tenga permisos de escritura.',
                ]);
            }

            $user->avatar = $newAvatar;
        }

        try {
            $user->save();
        } catch (\Throwable $exception) {
            ImageStorage::delete($newAvatar);
            throw $exception;
        }

        if ($newAvatar && $previousAvatar !== $newAvatar) {
            $this->deleteAvatarFile($previousAvatar);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();
        $this->deleteAvatarFile($user->avatar);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    private function deleteAvatarFile(?string $avatar): void
    {
        $path = trim((string) $avatar);
        if ($path === '') {
            return;
        }

        // Compatibilidad con avatares guardados antes como URL o /storage/ruta.
        $urlPath = parse_url($path, PHP_URL_PATH);
        $path = ltrim((string) ($urlPath ?: $path), '/');
        $path = preg_replace('#^storage/#', '', $path) ?: $path;

        ImageStorage::delete($path);
    }
}
