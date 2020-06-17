<?php

namespace App\Controller;

use App\Entity\Book;
use App\Util\Cart;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    protected $cart;

    public function __construct()
    {
        $storage = new NativeSessionStorage();

        $attributes = new NamespacedAttributeBag();

        $session = new Session($storage, $attributes);

        $this->cart = new Cart($session);
    }

    /**
     * Shows the shopping card details
     *
     * @Route("/cart", name="cart")
     */
    public function index()
    {
        return $this->render('cart/index.html.twig', [
            'items' => $this->cart->getItems(),
            'total' => $this->cart->getTotal(),
            'coupon' => $this->cart->getCoupon(),
            'discount' => $this->cart->getDiscount(),
        ]);
    }

    /**
     * Adds book to the cart
     *
     * @Route("/cart/add/{id}", name="cart.add")
     * @param Book $book
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addBookToCart(Book $book, Request $request)
    {
        $this->cart->addItem([
            'book_id' => $book->getId(),
            'title' => $book->getTitle(),
            'category_id' => $book->getCategory()->getId(),
            'category_name' => $book->getCategory()->getName(),
            'price' => $book->getPrice()
        ]);

        $this->addFlash('success', 'Book added to the cart!');

        $redirectTo = $this->generateUrl('home', ['category' => $request->get('category')]);

        return $this->redirect($redirectTo);
    }

    /**
     * Remove book from the cart
     *
     * @Route("/cart/remove/{id}", name="cart.remove")
     * @param Book $book
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeBookFromCart(Book $book)
    {
        $this->cart->removeItem($book->getId());

        $this->addFlash('success', 'Book removed from the cart!');

        return $this->redirect($this->generateUrl('cart'));
    }

    /**
     * Apply the coupon
     *
     * @Route("/cart/coupon", name="cart.add_coupon")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addCoupon(Request $request)
    {
        // TODO: Do the validation for check whether the coupon is valid or not
        $coupon = $request->get('coupon', null);

        if ($coupon) {
            $this->cart->addCoupon($coupon);

            $this->addFlash('success', 'Coupon redeemed successfully.');
        } else {
            $this->addFlash('danger', 'Coupon code cannot be empty.');
        }

        return $this->redirect($this->generateUrl('cart'));
    }

    /**
     * Checkout
     *
     * @Route("/cart/checkout", name="cart.checkout")
     */
    public function checkOutAction()
    {
        $cartItems = $this->cart->getItems();
        $cartTotal = $this->cart->getTotal();
        $discount = $this->cart->getDiscount();
        $coupon = $this->cart->getCoupon();
        // TODO: create an order

        try {
            $this->addFlash('success', 'Checkout completed.');

            $this->cart->clearCart();
        } catch (\Exception $exception) {
            $this->addFlash('danger', 'Error'); // need to log the exception details
        }

        return $this->render('cart/invoice.html.twig', [
            'items' => $cartItems,
            'total' => $cartTotal,
            'coupon' => $coupon,
            'discount'=> $discount,
        ]);
    }
}
