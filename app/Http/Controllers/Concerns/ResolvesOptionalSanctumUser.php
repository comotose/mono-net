<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

trait ResolvesOptionalSanctumUser
{
    protected function optionalSanctumUser(Request $request): ?User
    {
        if (! $token = $request->bearerToken()) {
            return null;
        }

        $accessToken = PersonalAccessToken::findToken($token);

        return $accessToken?->tokenable instanceof User ? $accessToken->tokenable : null;
    }
}
