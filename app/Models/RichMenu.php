<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RichMenu extends Model
{
    protected $fillable = [
        'rich_menu_id', 'name', 'chat_bar_text', 'button_uris', 'image_url'
    ];

    protected $casts = [
        'button_uris' => 'array',
    ];
}