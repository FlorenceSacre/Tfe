<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class video extends Model
{
    protected $table = 'video';

    public $timestamps = false;

    protected $guarded = [];

    protected $fillable = [
        'id','titre', 'categorie', 'videoBQ', 'videoHQ' ,'image'
    ];
    public function comments() {
        return $this->hasMany(Comments::class);
    }
}