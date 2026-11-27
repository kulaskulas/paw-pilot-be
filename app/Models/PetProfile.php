<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['user_id', 'name', 'species', 'breed', 'gender', 'date_of_birth'])]
class PetProfile extends Model
{
}
