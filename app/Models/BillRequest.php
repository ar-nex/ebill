<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillRequest extends Model
{
    use HasFactory;
     /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    // user
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
