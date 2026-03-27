<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'company_name' => 'required|string|max:255',
            'company_slug' => 'required|string|max:100|alpha_dash',
            'company_phone' => 'nullable|string|max:30',
        ]);

        $slug = $this->resolveUniqueSlug($request->string('company_slug')->toString());

        $company = Company::query()->create([
            'name' => $request->string('company_name')->toString(),
            'slug' => $slug,
            'phone' => $request->string('company_phone')->toString() ?: null,
            'email' => $request->string('email')->toString(),
        ]);

        $user = User::query()->create([
            'company_id' => $company->id,
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => Hash::make($request->string('password')->toString()),
            'role' => UserRole::Owner,
            'status' => UserStatus::Active,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    private function resolveUniqueSlug(string $slug): string
    {
        $baseSlug = Str::slug($slug);
        $candidate = $baseSlug;
        $counter = 2;

        while (Company::query()->where('slug', $candidate)->exists()) {
            $candidate = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $candidate;
    }
}
