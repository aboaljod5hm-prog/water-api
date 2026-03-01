<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'meter_no', 'section_no', 'reading', 'reading_date'];

    protected $casts = [
        'reading_date' => 'date:Y-m-d',
        'reading'      => 'float',
    ];
}
