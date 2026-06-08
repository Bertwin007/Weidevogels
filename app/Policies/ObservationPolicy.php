<?php

namespace App\Policies;

use App\Models\Observation;
use App\Models\User;

class ObservationPolicy
{
    public function delete(User $user, Observation $observation): bool
    {
        return $user->isAnnotator();
    }

    public function unpublish(User $user, Observation $observation): bool
    {
        return $user->isAdmin();
    }
}
