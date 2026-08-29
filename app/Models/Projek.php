<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'foto', 'nama_projek', 'deskripsi', 'link_web'])]
class Projek extends Model
{
    public function user() {
        return $this->belongsTo(User::class);
    }
}
