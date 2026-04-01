<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StatusPageCategory extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'sort_order'];

    public function statusPages(): HasMany
    {
        return $this->hasMany(StatusPage::class, 'category_id');
    }

    public function publicPages(): HasMany
    {
        return $this->hasMany(StatusPage::class, 'category_id')
            ->where('is_public', true);
    }
}
