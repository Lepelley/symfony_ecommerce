<?php

namespace App\Controller;

use App\Entity\Basket;
use App\Entity\Order;
use App\Entity\Product;
use App\Repository\BasketRepository;
use App\Repository\OrderRepository;
use App\Service\BasketService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_USER")
 */
class BasketController extends AbstractController
{

    private BasketRepository $repository;

    private EntityManagerInterface $manager;

    private SessionInterface $session;

    private BasketService $basketService;

    public function __construct(
        BasketRepository $repository,
        EntityManagerInterface $manager,
        SessionInterface $session,
        BasketService $basketService
    ) {
        $this->repository = $repository;
        $this->manager = $manager;
        $this->session = $session;
        $this->basketService = $basketService;
    }

    /**
     * @Route("/basket", name="basket")
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('basket/index.html.twig', [
            'basket' => $this->basketService->findCurrent(),
        ]);
    }

    /**
     * @Route("/basket/add/{id}", name="basket_add")
     *
     * @param Product $product
     *
     * @return Response
     */
    public function add(Product $product): Response
    {
        $this->basketService->add($product);

        return $this->redirectToRoute('basket');
    }

    /**
     * @Route("/basket/substract/{id}", name="basket_substract")
     *
     * @param Product $product
     *
     * @return Response
     */
    public function substract(Product $product): Response
    {
        $this->basketService->substract($product);

        return $this->redirectToRoute('basket');
    }

    /**
     * @Route("/basket/delete/{id}", name="basket_delete")
     *
     * @param Product         $product
     *
     * @return Response
     */
    public function delete(Product $product): Response
    {
        $this->basketService->delete($product);

        return $this->redirectToRoute('basket');
    }

    /**
     * @Route("/basket/validate", name="basket_validate")
     *
     * @return Response
     */
    public function validate(): Response
    {
        $this->basketService->validate();

        return $this->redirectToRoute('basket_list');
    }

    /**
     * @Route("/basket/list", name="basket_list")
     *
     * @return Response
     */
    public function list(): Response
    {
        return $this->render('basket/list.html.twig', [
            'baskets' => $this->repository->findBy(['isOrdered' => true]),
        ]);
    }
}
