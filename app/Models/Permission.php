<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;
    protected $fillable = [
        "board_id",
        "user_id",
        "edit_cards",
        "delete_cards",
        "add_cards",
        "add_members",
        "manage_board",
    ] ;

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function board(){
        return $this->belongsTo(Board::class);
    }
}
