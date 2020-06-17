<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {

        $fiction = new Category();
        $fiction->setName('fiction');
        $manager->persist($fiction);

        $children = new Category();
        $children->setName('children');
        $manager->persist($children);

        $manager->flush();


        $childrenBooks = [
            'Amelia Bedelia' => '200',
            'The Arrival' => '300',
            'Bark, George' => '400',
            'Ben’s Trumpet' => '500',
            'Because of Winn-Dixie' => '200',
            'Big Red Lollipop' => '400',
            'The Book of Three' => '500',
            'The Borrowers' => '200'
        ];

        foreach($childrenBooks as $name => $price){
            $book = new Book();
            $book->setTitle($name);
            $book->setPrice($price);
            $book->setCategory($children);
            $manager->persist($book);
        }

        $fictionBooks = [
            'The Pilgrim’s Progress' => '200',
            'Gulliver’s Travels' => '300',
            'Clarissa' => '400',
            'Tom Jones' => '500'
        ];

        foreach($fictionBooks as $name => $price){
            $book = new Book();
            $book->setTitle($name);
            $book->setPrice($price);
            $book->setCategory($fiction);
            $manager->persist($book);
        }




        $manager->flush();
    }
}
