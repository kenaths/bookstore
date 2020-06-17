<?php


namespace App\Util;


use App\Entity\Book;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Cart
{
    CONST CHILD_CATEGORY = 2;
    CONST CHILD_CATEGORY_MIN_QTY = 5;
    CONST CHILD_CATEGORY_DISCOUNT = 10;

    CONST TOTAL_BILL_ADDITIONAL_DISCOUNT = 5;
    CONST TOTAL_BILL_DISCOUNT_EACH_QTY = 10;

    CONST FICTION_CATEGORY = 1;

    CONST COUPON_DISCOUNT = 15;

    protected $session;

    protected $coupon;

    protected $discounts = [];

    protected $booksCountByCategory = [];

    protected $booksPriceTotalByCategory = [];

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function getCoupon()
    {
        return $this->session->get('coupon', null);
    }

    public function setCoupon($coupon)
    {
        $this->session->set('coupon', $coupon);

        return $this;
    }

    public function getItems()
    {
        return $this->session->get('cart', []);
    }

    public function setItems($items)
    {
        $this->session->set('cart', $items);

        return $this;
    }

    public function getTotal()
    {
        return $this->session->get('total', 0);
    }

    public function setTotal($total)
    {
        $this->session->set('total', $total);

        return $this;
    }

    public function getDiscount()
    {
        return $this->session->get('discount', 0);
    }

    public function setDiscount($discount)
    {
        $this->session->set('discount', $discount);

        return $this;
    }

    public function addItem($item)
    {
        $cart = $this->getItems();

        $bookId = $item['book_id'];

        if (isset($cart[$bookId])) {
            $cart[$bookId]['qty'] += 1;
        } else {
            $cart[$bookId] = array_merge($item, [ 'qty' => 1 ]);
        }

        $this->setItems($cart);

        $this->calculateTotal();

        $this->calculateDiscounts();
    }

    public function removeItem($bookId)
    {
        $cart = $this->getItems();

        if (isset($cart[$bookId])) {
            unset($cart[$bookId]);
        }

        $this->setItems($cart);

        $this->calculateTotal();

        $this->calculateDiscounts();
    }

    public function calculateTotal()
    {
        $items = $this->getItems();

        $total = 0;
        $this->booksCountByCategory = [];
        $this->booksPriceTotalByCategory = [];

        foreach ($items as $item) {
            $total += $item['qty'] * $item['price'];

            $category = $item['category_id'];

            if(!isset($this->booksCountByCategory[$category])){
                $this->booksCountByCategory[$category] = 0;
            }
            if(!isset($this->booksPriceTotalByCategory[$category])){
                $this->booksPriceTotalByCategory[$category] = 0;
            }
            $this->booksCountByCategory[$category] += $item['qty'];
            $this->booksPriceTotalByCategory[$category] += $item['qty'] * $item['price'];
        }

        $this->setTotal($total);
    }

    public function calculateDiscounts()
    {
        $total = $this->getTotal();

        if ($this->getCoupon()) {
            $this->setDiscount($total * (self::COUPON_DISCOUNT / 100));

            return;
        }

        $discounts = 0;

        $childCategoryBooksCount = isset($this->booksCountByCategory[self::CHILD_CATEGORY])
            ? $this->booksCountByCategory[self::CHILD_CATEGORY] : 0;

        if ($childCategoryBooksCount >= self::CHILD_CATEGORY_MIN_QTY) {
            $childCategoryBooksPrice = isset($this->booksPriceTotalByCategory[self::CHILD_CATEGORY])
                ? $this->booksPriceTotalByCategory[self::CHILD_CATEGORY] : 0;
            $discounts += $childCategoryBooksPrice * self::CHILD_CATEGORY_DISCOUNT/ 100;
        }

        $fictionCategoryBooksCount = isset($this->booksCountByCategory[self::FICTION_CATEGORY])
            ? $this->booksCountByCategory[self::FICTION_CATEGORY] : 0;

        if (
            $fictionCategoryBooksCount >= self::TOTAL_BILL_DISCOUNT_EACH_QTY
            && $childCategoryBooksCount >= self::TOTAL_BILL_DISCOUNT_EACH_QTY
        ) {
            $discounts += $total * self::TOTAL_BILL_ADDITIONAL_DISCOUNT / 100;
        }

        $this->setDiscount($discounts);
    }

    public function clearCart()
    {
        $this->setTotal(0)
            ->setItems([])
            ->setDiscount(0)
            ->setCoupon(null);

    }

    public function addCoupon($coupon)
    {
        $this->setCoupon($coupon);

        $this->calculateDiscounts();
    }
}
