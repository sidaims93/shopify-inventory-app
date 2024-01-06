<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopifyStore extends Model
{
    protected $primaryKey = 'table_id';
    protected $guarded = [];
    use HasFactory;
}
