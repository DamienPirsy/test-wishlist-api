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
     * @OA\Post(
     *     path="/api/v1/products/",
     *     security={
     *         {"bearer":{}}
     *     },
     *     tags={"products"},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Nome del prodotto",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sku",
     *         in="query",
     *         description="Codice univoco del prodotto",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="price",
     *         in="query",
     *         description="Prezzo",
     *         required=true,
     *         @OA\Schema(type="float")
     *     ),
     *     @OA\Parameter(
     *         name="description",
     *         in="query",
     *         description="Descrizione prodotto",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Prodotto creato con successo",
     *         @OA\JsonContent(
     *            @OA\Property(
     *              title="id",
     *              type="integer",
     *              description="Id prodotto creato"
     *            )
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Errore / Eccezione",
     *     )
     * )
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
     * Modifica prodotto
     * @todo
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
     * @OA\Delete(
     *     path="/api/v1/products/{id}",
     *     security={
     *         {"bearer":{}}
     *     },
     *     tags={"products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id prodotto da eliminare",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="204",
     *         description="Prodotto cancellato con successo"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Prodotto non trovato"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Errore / Eccezione",
     *     )
     * )
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