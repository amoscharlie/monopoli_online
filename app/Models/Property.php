<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    protected $fillable = [
        'name',
        'group_code',
        'group_name',
        'property_kind',
        'price',
        'house_price',
        'hotel_price',
        'rent',
        'rent_1_house',
        'rent_2_houses',
        'rent_3_houses',
        'rent_4_houses',
        'rent_hotel',
        'mortgage_value',
        'color',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'house_price' => 'integer',
            'hotel_price' => 'integer',
            'rent' => 'integer',
            'rent_1_house' => 'integer',
            'rent_2_houses' => 'integer',
            'rent_3_houses' => 'integer',
            'rent_4_houses' => 'integer',
            'rent_hotel' => 'integer',
            'mortgage_value' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function gameProperties(): HasMany
    {
        return $this->hasMany(GameProperty::class);
    }
}
