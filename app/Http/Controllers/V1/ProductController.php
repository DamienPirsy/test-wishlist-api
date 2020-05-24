<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Products;
use Illuminate\Support\Str; // carico l'helper stringhe

class ProductController extends Controller
{

    /**
     * Restituisce una o tutte le wishlist dell'utente loggato
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function fetch(Request $request, $id = null) 
    {
        try {
            if ($id) {
                $products = Products::where(['id' => $id])->get();
            } else {
                $products = Products::all();
            }
            // ^_ ->all() restituisce una Collection

            if ($products->isEmpty()) {
                return response()->json(['message' => 'Product not found'], 404);
            }
            return response()->json(['products' => $products, 'message' => 'OK'], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Aggiunge prodotto
     *
     * @param  Request  $request
     * @return Response
     */
    public function add(Request $request)
    {
        //validate incoming request 
        $this->validate($request, [
            'name' => 'required|string',
            'sku' => 'required|string|unique:products',
            'price' => 'required|numeric'
        ]);

        try {
            $product = new Products;
            $product->name = $request->input('name');
            $product->alias = Str::of($request->input('name'))->slug('-');
            $product->sku = $request->input('sku');
            $product->price = $request->input('price');
            $product->description = $request->input('description');
            $product->save();
            return response()->json(['id' => $product->id, 'message' => 'OK'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Modifica la wishlist
     *
     * @param Request $request
     * @param int $id
     * @return response
     */
    public function edit(Request $request, $id) {
        return response()->json(['message' => 'Not implemented yet!'], 501);
    }

    /**
     * Deletes by index key
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function remove(Request $request, $id) {
        try {
            // Solo l'utente loggato può eliminare una sua wishlist
            if (Products::where(['id' => $id])->delete()) {
                return response()->json(['message' => 'OK'], 200);
            } else {
                return response()->json(['message' => 'Product not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}