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
            // ^_ anche all() restituisce una Collection

            if ($products->isEmpty()) {
                return $this->genericErrorResponse('NOT FOUND', ['message' => 'Product not found'], 404);
            }
            return $this->genericSuccessResponse('OK', ['items' => $products, 'count' => count($products)]);

        } catch (\Exception $e) {
            return $this->genericErrorResponse($e->getMessage());
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
            return $this->genericSuccessResponse('CREATED', ['id' => $product->id]);
        } catch (\Exception $e) {
            return $this->genericErrorResponse($e->getMessage());
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
        return $this->genericErrorResponse('NOT IMPLEMENTED', 501);
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
            if (Products::where(['id' => $id])->delete()) {
                return $this->genericSuccessResponse('DELETED', [], 204);
            } else {
                return $this->genericErrorResponse('NOT FOUND', ['message' => 'Product not found'], 404);
            }
        } catch (\Exception $e) {
            return $this->genericErrorResponse($e->getMessage());
        }
    }
}