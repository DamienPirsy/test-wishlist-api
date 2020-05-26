<?php

use Illuminate\Support\Str;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AppTest extends TestCase
{

    //use DatabaseTransactions;

    /** 
     * @test 
     * Questa funzionalità mi serve per la crazione della wishlist
     * */
    public function should_create_product()
    {       
        $sku = Str::random();
        $data = ['name' => 'prodotto1', 'sku' => $sku, 'price' => 1.99];
        $this->asLoggedUser()
             ->post('api/v1/products', $data)
             ->seeStatusCode(201)
             ->seeInDatabase('products', ['sku' => $sku]);
    }

    /**
     * @test
     */
    public function should_delete_product()
    {
        $product = factory(App\Products::class)->create();
        $this->asLoggedUser()
            ->delete("api/v1/products/{$product->id}")
            ->seeStatusCode(204)
            ->notSeeInDatabase('products', ['id' => $product->id]);
    }
        
    /**
     * Creo 5 wishlist con lo stesso utente e verifico che le recuperi tutte
     * @test
     */
    public function should_get_all_wishlist()
    {
        $user = $this->makeUser();
        factory(App\Wishlist::class, 5)->create([
            'user_id' => $user->id
        ]);
        $this->asLoggedUser($user)
                ->get('api/v1/wishlists')
                ->seeStatusCode(200)
                ->assertCount(5, json_decode($this->response->getContent())->items);
    }

    /**
     * Creo 3 liste con un'altra utenza, non dovrei recuperarne nessuna
     * @test
     */
    public function should_not_get_all_wishlist()
    {
        $user = $this->makeUser();
        factory(App\Wishlist::class, 3)->create([
            'user_id' => 999
        ]);
        $this->asLoggedUser($user)
                ->get('api/v1/wishlists')
                ->seeStatusCode(404)
                ->seeJson([
                   'error' => 'NOT FOUND'
                ]);
    }

    /**
     * Crea una wishlist e verifica che si possa recuperare
     * @test
     */
    public function should_get_wishlist()
    {
        $user = $this->makeUser();
        $list = factory(App\Wishlist::class)->create([
            'user_id' => $user->id
        ]);
        $this->asLoggedUser($user)
             ->get("api/v1/wishlists/{$list->id}")
             ->seeStatusCode(200)
            ->seeJson([
                'message' => 'OK'
            ]);
    }

    /**
     * Utente non dovrebbe vedere la wishlist che non ha creato
     * @test
     */
    public function should_not_get_wishlist()
    {
        $user = $this->makeUser();
        $list = factory(App\Wishlist::class)->create([
            'user_id' => $user->id
        ]);
        $other = factory(App\Wishlist::class)->create([
            'user_id' => 999
        ]);
        $this->asLoggedUser($user)
             ->get("api/v1/wishlists/{$other->id}")
             ->seeStatusCode(404)
             ->seeJson([
                'error' => 'NOT FOUND'
             ]);
    }

    /** @test */
    public function should_create_wishlist()
    {
        $data = ['name' => 'test1'];
        $this->asLoggedUser()
            ->post('api/v1/wishlists', $data)
            ->seeStatusCode(201)
            ->seeInDatabase('wishlists', ['name' => 'test1'])
            ->seeJson([
                'message' => 'CREATED',
            ]);;
    }

    /** @test */
    public function should_edit_wishlist()
    {
        $user = $this->makeUser();
        $list = factory(App\Wishlist::class)->create([
            'user_id' => $user->id,
            'name' => 'prova',
            'alias' => 'prova'
        ]);
        $newData = ['name' => 'modificato'];

        $this->asLoggedUser($user)
            ->put("api/v1/wishlists/{$list->id}", $newData)
            ->seeStatusCode(200)
            ->seeInDatabase('wishlists', ['name' => 'modificato'])
            ->seeJson([
                'message' => 'UPDATED'
            ]);
    }

    /** @test */
    public function should_not_edit_wishlist()
    {
        $user = $this->makeUser();
        $list = factory(App\Wishlist::class)->create([
            'user_id' => 999,
            'name' => 'prova_ko',
            'alias' => 'prova_ko'
        ]);
        $newData = ['name' => 'modifica_ko'];

        $this->asLoggedUser($user)
            ->put("api/v1/wishlists/{$list->id}", $newData)
            ->seeStatusCode(401)
            ->notSeeInDatabase('wishlists', ['name' => 'modifica_ko'])
            ->seeJson([
                'error' => 'UNAUTHORIZED'
            ]);
    }

    /** 
     * SOlo gli utenti che hanno creato una wishlist la possono cancellare
     * @test 
     * 
    */
    public function should_not_delete_wishlist()
    {
        $user = $this->makeUser();
        $list = factory(App\Wishlist::class)->create([
            'user_id' => 999,
            'name' => 'prova',
            'alias' => 'prova'
        ]);
        $newData = ['name' => 'modificato'];

        $this->asLoggedUser($user)
            ->put("api/v1/wishlists/{$list->id}", $newData)
            ->seeStatusCode(401)
            ->seeJson([
                'error' => 'UNAUTHORIZED'
            ]);
    }

    /** @test */
    public function should_delete_wishlist()
    {
        $user = $this->makeUser();
        $list = factory(App\Wishlist::class)->create([
            'user_id' => $user->id
        ]);
        $this->asLoggedUser($user)
            ->delete("api/v1/wishlists/{$list->id}")
            ->seeStatusCode(204)
            ->notSeeInDatabase('wishlists', ['id' => $list->id]);
    }

    /** @test */
    public function should_add_product()
    {
        $user = $this->makeUser();
        $list = factory(App\Wishlist::class)->create([
            'user_id' => $user->id
        ]);
        $product = factory(App\Products::class)->create();       
        $this->asLoggedUser($user)
             ->post("api/v1/wishlists/{$list->id}/products", ['pid' => $product->id])
             ->seeStatusCode(201)
             ->seeInDatabase('products_wishlist', ['products_id' => $product->id, 'wishlist_id' => $list->id])
             ->seeJson([
                 'message' => 'CREATED'
             ]);
    }

    /** @test */
    public function should_get_products()
    {
        $user = $this->makeUser();
        $list = factory(App\Wishlist::class)->create([
            'user_id' => $user->id
        ]);
        $product = factory(App\Products::class)->create();
        $list->products()->attach($product);
        
        $this->asLoggedUser($user)
             ->get("api/v1/wishlists/{$list->id}/products")
             ->seeStatusCode(200);
    }

    /**
     * Prova ad aggiungere il prodotto ad un'altra lista non sua
     * @test
     */
    public function should_not_add_product()
    {
        $list = factory(App\Wishlist::class)->create([
            'user_id' => 999
        ]);
        $product = factory(App\Products::class)->create();
        $this->asLoggedUser()
             ->post("api/v1/wishlists/{$list->id}/products", ['pid' => $product->id])
             ->seeStatusCode(401)
             ->seeJson([
                 'error' => 'UNAUTHORIZED'
             ]);
    }

    /** @test
     * Inserire 2 volte lo stesso prodotto alla lista non dovrebbe essere possibile
     */
    public function product_should_be_present()
    {
        $user = $this->makeUser();
        $list = factory(App\Wishlist::class)->create([
            'user_id' => $user->id
        ]);
        $product = factory(App\Products::class)->create();
        $this->asLoggedUser($user)
             ->post("api/v1/wishlists/{$list->id}/products", ['pid' => $product->id])
             ->post("api/v1/wishlists/{$list->id}/products", ['pid' => $product->id])
             ->seeStatusCode(301)
             ->seeJson([
                 'message' => 'NOT ADDED'
             ]);   
    }

    /** @test
     * cancella prodotto dalla lista
     */
    public function should_remove_one_product()
    {
        $user = $this->makeUser();
        $list = factory(App\Wishlist::class)->create([
            'user_id' => $user->id
        ]);
        $product = factory(App\Products::class)->create();
        $this->asLoggedUser($user)
             ->post("api/v1/wishlists/{$list->id}/products", ['pid' => $product->id])
             ->delete("api/v1/wishlists/{$list->id}/products", ['pid' => $product->id])
             ->seeStatusCode(204);
    }

    /** 
     * @test
     * cancella prodotti dalla lista
     * Verifico che il numero di quelli trovati sia 0
     */
    public function should_remove_all_products()
    {
        $user = $this->makeUser();
        $list = factory(App\Wishlist::class)->create([
            'user_id' => $user->id
        ]);
        $products = factory(App\Products::class, 3)->create();
        $list->products()->saveMany($products);

        $this->asLoggedUser($user)
             ->delete("api/v1/wishlists/{$list->id}/products")
             ->seeStatusCode(204)
             ->assertCount(0, json_decode(
                 $this->asLoggedUser($user)
                 ->get("api/v1/wishlists/{$list->id}/products")
                 ->response->getContent())->items
             );
    }
}
