<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conference extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'year', 'created_by'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pages()
    {
        return $this->hasMany(Page::class);
    }

    public function editors()
    {
        return $this->hasMany(Editor::class);
    }
}
