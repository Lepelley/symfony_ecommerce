<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     *
     * @param CategoryRepository $categoryRepository
     * @param ProductRepository  $productRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(CategoryRepository $categoryRepository, ProductRepository $productRepository)
    {
//        $products = $productRepository->findAll();
//        if (is_array($products)) {
//            shuffle($products);
//            $products = array_slice($products, 0, 3);
//        } else {
//            throw new \Exception('RIP');
//        }
        $products = $productRepository->getRandomProducts();

        return $this->render('home/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
            'products' => $products,
        ]);
    }
}
