<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Wishlist extends Model 
{
    /**
     * I prodotti di questa wishlist
     */
    public function products()
    {
        return $this->belongsToMany('App\Products');
    }

    /**
     * Report completo delle wishlist
     *
     * @param string $filepath
     * @return boolean
     */
    public function getReport($filepath) 
    {
        $sql = sprintf("SELECT u.email AS `user`, w.name AS `title wishlist`,
            (SELECT COUNT(pw.id) FROM products_wishlist pw 
            WHERE pw.wishlist_id = w.id) AS `number of items`
        INTO OUTFILE '%s'
            FIELDS TERMINATED BY ';' OPTIONALLY ENCLOSED BY '\"'
            LINES TERMINATED BY '\n'
        FROM users u
        JOIN wishlists w ON w.user_id = u.id", $filepath);

        DB::beginTransaction();
        try {
            DB::statement($sql);
            DB::commit();
            Log::debug(sprintf("Load data ok: %s", $filepath));
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return false;
        }
    }

}
