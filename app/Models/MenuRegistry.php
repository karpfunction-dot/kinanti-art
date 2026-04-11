<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuRegistry extends Model
{
    protected $table = 'menu_registry';
    protected $primaryKey = 'id_menu';
    public $timestamps = false; // Matikan timestamps

    protected $fillable = ['id_parent', 'label', 'icon', 'page', 'id_role', 'order_index', 'aktif'];
}