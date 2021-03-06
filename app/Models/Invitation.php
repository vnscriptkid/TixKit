<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    use HasFactory;

    public static function findByCode(string $code)
    {
        return self::where('code', $code)->firstOrFail();
    }

    public function hasBeenUsed()
    {
        return $this->user_id !== null;
    }
}
