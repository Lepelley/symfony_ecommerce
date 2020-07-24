<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{

    /**
     * @Route("/category/new", name="category_new")
     * @IsGranted("ROLE_USER")
     *
     * @param Request                $request
     * @param EntityManagerInterface $manager
     *
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $manager)
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (null === $category->getSlug()) {
                $category->setSlug((new Slugify())->slugify($category->getName()));
            }

            $manager->persist($category);
            $manager->flush();

            $this->addFlash('success', "La catégorie {$category->getName()} a été ajouté !");

            return $this->redirectToRoute('category_show', [
                'slug' => $category->getSlug(),
            ]);
        }

        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/category/{slug}", name="category_show")
     *
     * @param Category $category
     *
     * @return Response
     */
    public function index(Category $category)
    {
        return $this->render('category/index.html.twig', [
            'category' => $category,
        ]);
    }
}
