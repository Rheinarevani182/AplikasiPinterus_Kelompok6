<?php

namespace App\Policies;

use App\Models\Pin;
use App\Models\User;

class PinPolicy
{
    public function delete(User $user, Pin $pin): bool
    {
        return $pin->user_id === $user->id;
    }
}
