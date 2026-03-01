<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = ['name', 'username', 'password', 'role'];
    protected $hidden   = ['password', 'remember_token'];

    const ROLES = [
        'admin_general' => 'المدير العام',
        'admin'         => 'مدير الدائرة',
        'worker'        => 'عامل قراءات',
    ];
    public function getAuthIdentifierName() { return 'username'; }
}

