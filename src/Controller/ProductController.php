<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProductController extends AbstractController
{
    /**
     * @Route("/product/{slug}/{slugProduct}", name="product_show")
     *
     * @param Category $category
     * @param Product  $product
     *
     * @return Response
     */
    public function index(Category $category, Product $product)
    {
        return $this->render('product/index.html.twig', [
            'category' => $category,
            'product' => $product,
        ]);
    }

    /**
     * @Route("/search", name="product_search")
     *
     * @param ProductRepository $repository
     * @param Request           $request
     *
     * @return Response
     */
    public function search(ProductRepository $repository, Request $request)
    {
        $search = $request->get('value');
        $products = $repository->search($search);

        return $this->render('product/search.html.twig', [
            'search' => $search,
            'products' => $products,
        ]);
    }

    /**
     * @Route("/product/new", name="product_new")
     * @IsGranted("ROLE_USER")
     *
     * @param EntityManagerInterface $manager
     * @param Request                $request
     *
     * @param SluggerInterface       $slugger
     *
     * @return Response
     * @throws \Exception
     */
    public function create(EntityManagerInterface $manager, Request $request, SluggerInterface $slugger)
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $pictureFile */
            $pictureFile = $form->get('pictureFilename')->getData();

            if ($pictureFile) {
                $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$pictureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $pictureFile->move(
                        $this->getParameter('picture_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw new \Exception('bug');
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $product->setPicture($newFilename);
            }
            if (null === $product->getSlugProduct()) {
                $product->setSlugProduct((new Slugify())->slugify($product->getName()));
            }

            $manager->persist($product);
            $manager->flush();

            $this->addFlash('success', "Le produit {$product->getName()} a été ajouté !");

            return $this->redirectToRoute('product_show', [
                'slug' => $product->getCategory()->getSlug(),
                'slugProduct' => $product->getSlugProduct(),
            ]);
        }

        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
