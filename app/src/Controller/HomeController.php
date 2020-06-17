<?php

namespace App\Controller;

use App\Repository\BookRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * List all books
     *
     * @Route("/", name="home")
     * @param BookRepository $bookRepository
     * @param CategoryRepository $categoryRepository
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(BookRepository $bookRepository, CategoryRepository $categoryRepository, Request $request)
    {
        $categories = $request->get('category');

        if(! empty($categories)){
            $books = $bookRepository->findByCategory($categories);
        } else {
            $books = $bookRepository->findAll();
        }

        return $this->render('home/index.html.twig', [
            'filter' => $categories,
            'books' => $books,
            'categories' => $categoryRepository->findAll()
        ]);
    }
}
