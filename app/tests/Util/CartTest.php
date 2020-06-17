<?php

namespace App\Tests\Util;

use App\Util\Cart;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class CartTest extends TestCase
{
    protected $cart;

    function setUp()
    {
        @session_start();
        parent::setUp();
    }

    public function __construct()
    {
        parent::__construct();

        $this->setUp();

        $storage = new NativeSessionStorage();
        $attributes = new NamespacedAttributeBag();
        $session = new Session($storage, $attributes);
        $this->cart = new Cart($session);
    }

    private function addItemToTheCart($props = [])
    {
        $this->cart->addItem(array_merge(
            [
                'book_id' => 1,
                'title' => 'Test title',
                'category_id' => Cart::CHILD_CATEGORY,
                'category_name' => 'children',
                'price' => '100.00'
            ],
            $props
        ));

        return $this;
    }

    public function testCanAddNewProductToTheCart()
    {
        $this->cart->clearCart();

        $this->addItemToTheCart();

         $items = $this->cart->getItems();

         $this->assertTrue(count($items) == 1);
    }

    public function testCanIncreaseQty()
    {
        $this->cart->clearCart();

        $bookId = 1;

        $this->addItemToTheCart(['book_id' => $bookId]);

        $this->addItemToTheCart(['book_id' => $bookId]);

        $items = $this->cart->getItems();

        $qty = 0;
        foreach($items as $item){
            if($item['book_id'] == $bookId){
                $qty = $item['qty'];
            }
        }

        $this->assertTrue($qty == 2);
    }

    public function testCanRemoveItemFromCart()
    {
        $this->cart->clearCart();

        $bookId = 1;

        $this->addItemToTheCart(['book_id' => $bookId]);

        $this->cart->removeItem($bookId);

        $items = $this->cart->getItems();

        $this->assertTrue(count($items) == 0);
    }

    public function testOnlyChildrenBooksDiscount()
    {
        $this->cart->clearCart();

        $props = [
            'book_id' => 1, 'price' => '100'
        ];

        $this->addItemToTheCart($props)
            ->addItemToTheCart($props)
            ->addItemToTheCart($props)
            ->addItemToTheCart($props)
            ->addItemToTheCart($props);

        $discount = $this->cart->getDiscount();

        $this->assertEquals((5 * 100 * 10/100), $discount);
    }

    public function testCouponDiscount()
    {
        $this->cart->clearCart();

        $props = [
            'book_id' => 1, 'price' => '100'
        ];

        $this->addItemToTheCart($props)
            ->addItemToTheCart($props)
            ->addItemToTheCart($props)
            ->addItemToTheCart($props)
            ->addItemToTheCart($props)
            ->addItemToTheCart($props);

        $this->cart->addCoupon('12345');

        $discount = $this->cart->getDiscount();

        $this->assertEquals((6 * 100 * 15/100), $discount);
    }

    public function testAdditionalDiscount()
    {
        $this->cart->clearCart();

        for($i = 0; $i < 10; $i++){
            $this->addItemToTheCart(['book_id' => 1, 'price' => '100']);
            $this->addItemToTheCart(
                ['book_id' => 2, 'price' => '200', 'category_id' => Cart::FICTION_CATEGORY]
            );
        }

        $this->assertEquals((100*10 + (200 * 10)), $this->cart->getTotal());

        $discount = $this->cart->getDiscount();

        $actual = (100 * 10 * 10/100) + ((100*10 + (200 * 10)) * 5 / 100);

        $this->assertEquals($actual, $discount);
    }
}
