<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Office;

class OfficePolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function update(User $user, Office $office)
    {
        //
        return $user->id == $office->user_id;
    }

    //
    public function delete(User $user, Office $office)
    {
        //
        return $user->id == $office->user_id;
    }    

}
