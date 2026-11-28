<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['user_id', 'first_name', 'last_name', 'phone_number', 'address'])]
class UserProfile extends Model
{
    //

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
