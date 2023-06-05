<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Token extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'expired_at'
    ];

    protected $hashable = [
        'token'
    ];

    // Always store the hashed form of a token in the database
    public function setTokenAttribute($value)
    {
        $this->attributes['token'] = Hash::make($value);
    }

    /**
     * @return The function `is_expired()` is returning a boolean value. It will return `true` if the
     * `expired_at` property of the object is in the past (i.e. has already expired), and `false`
     * otherwise.
     */
    public function is_expired()
    {
        return !Carbon::parse($this->expired_at)->timestamp > Carbon::now()->timestamp;
    }
}
