<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero',
        'nom',
        'type_doc',
        'filename',
        'public_path',
        'storage_path',
    ];
}
