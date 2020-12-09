<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concert extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $dates = ['date', 'published_at'];

    public function scopePublished($query) {
        return $query->whereNotNull('published_at');
    }

    public function getTicketPriceInDollarsAttribute() {
        return number_format($this->ticket_price / 100, 2);
    }

    public function getFormattedDateAttribute() {
        return $this->date->isoFormat('MMMM DD, YYYY');
    }   

    public function getFormattedStartTimeAttribute() {
        return $this->date->isoFormat('h:mma');
    }
}
