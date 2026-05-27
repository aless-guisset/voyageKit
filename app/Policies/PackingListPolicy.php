<?php

namespace App\Policies;

use App\Models\PackingList;
use App\Models\User;

class PackingListPolicy
{
    public function view(User $user, PackingList $list): bool
    {
        return $user->id === $list->user_id;
    }

    public function update(User $user, PackingList $list): bool
    {
        return $user->id === $list->user_id;
    }

    public function delete(User $user, PackingList $list): bool
    {
        return $user->id === $list->user_id;
    }
}
