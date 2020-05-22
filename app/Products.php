<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Products extends Model 
{
    /**
     * Le wishlist che hanno questo prodotto
     */
    public function wishlists()
    {
        return $this->belongsToMany('App\Wishlist');
    }

}
