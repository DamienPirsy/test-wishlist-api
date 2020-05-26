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
     * Passare il parametro $header per mostrare o meno l'intestazione delle colonne
     *
     * @param string $filepath
     * @param boolean $header
     * @return boolean
     */
    public function getReport($filepath, $header = false) 
    {
        $sql = sprintf("%sSELECT u.email , w.name,
            (SELECT COUNT(pw.id) FROM products_wishlist pw 
            WHERE pw.wishlist_id = w.id) AS items
        INTO OUTFILE '%s'
            FIELDS TERMINATED BY ';' OPTIONALLY ENCLOSED BY '\"'
            LINES TERMINATED BY '\n'
        FROM users u
        JOIN wishlists w ON w.user_id = u.id", 
            $header ? "SELECT 'user', 'title wishlist', 'numder of items' UNION " : '',
            $filepath);

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
