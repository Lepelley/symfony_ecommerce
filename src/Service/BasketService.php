<?php

namespace App\Service;

use App\Entity\Basket;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\BasketRepository;
use App\Repository\OrderRepository;
use App\Repository\TrainingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class BasketService
{
    private BasketRepository $basketRepository;

    private OrderRepository $orderRepository;

    private SessionInterface $session;

    private Security $security;

    private EntityManagerInterface $manager;

    private FlashBagInterface $flash;

    public function __construct(
        BasketRepository $basketRepository,
        OrderRepository $orderRepository,
        SessionInterface $session,
        Security $security,
        EntityManagerInterface $manager,
        FlashBagInterface $flash
    ) {
        $this->manager = $manager;
        $this->basketRepository = $basketRepository;
        $this->orderRepository = $orderRepository;
        $this->session = $session;
        $this->security = $security;
        $this->flash = $flash;
    }

    public function findCurrent(): Basket
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $basket = $this->basketRepository->findBasket($user);
        if (false === $basket) {
            $basket = new Basket();
            $basket
                ->setUser($user)
                ->setIsOrdered(false)
            ;
            $this->manager->persist($basket);
            $this->manager->flush();
        }

        return $basket;
    }

    public function add(Product $product): void
    {
        if (false === $product->getInStock()) {
            $this->flash->add('danger', "{$product->getName()} n'est plus en stock, désolé !" );

            return;
        }

        $basket = $this->findCurrent();
        $order = $this->findOrder($basket, $product, $this->basketRepository);
        $order = $this->addQuantity($order);
        $this->manager->persist($order);

        $basket->addOrder($order);
        $this->manager->persist($basket);
        $this->manager->flush();

        $this->session->set('basket', $this->session->get('basket') + 1);
        $this->flash->add('success', "{$product->getName()} a été ajouté au panier");
    }

    public function getBasketNumberOfItems(): int
    {
        if (null !== $this->session->get('basket')) {
            return $this->session->get('basket');
        }

        $basket = $this->basketRepository->findOneBy([
            'user' => $this->security->getUser(),
            'isOrdered' => false,
        ]);

        $this->session->set('basket', $basket->getOrders()->count());
        return $this->session->get('basket');
    }

    public function validate(): void
    {
        $basket = $this->findCurrent();
        if ($basket->getOrders()->isEmpty()) {
            $this->flash->add('danger', 'Panier non commandé car vide !');
            return;
        }

        $basket->setIsOrdered(true);
        $this->flash->add('success', 'Panier commandé !');
        $this->manager->persist($basket);
        $this->manager->flush();

        $this->session->set('basket', 0);
        $newBasket = new Basket();
        $newBasket->setIsOrdered(false)->setUser($this->security->getUser());
        $this->manager->persist($newBasket);
        $this->manager->flush();

        return;
    }

    public function substract(Product $product): void
    {
        $basket = $this->findCurrent();
        $order = $this->findOrder($basket, $product);
        $order = $this->substractQuantity($order);
        if ($order->getQuantity() < 1) {
            $basket->removeOrder($order);
            $this->manager->remove($order);
            $this->session->set('basket', 0);
        } else {
            $this->manager->persist($order);
            $basket->addOrder($order);
            $this->session->set('basket', $this->session->get('basket') - 1);
        }

        $this->manager->persist($basket);
        $this->manager->flush();

        $this->flash->add('success', "{$product->getName()} a été enlevé du panier");

        return;
    }

    public function delete(Product $product)
    {
        $basket = $this->findCurrent();
        $order = $this->findOrder($basket, $product);
        if (false === $order) {
            $this->flash->add('danger', 'Ce produit n\'était pas dans votre panier');

            return;
        }
        $basket->removeOrder($order);
        $this->manager->remove($order);
        $this->manager->flush();
        $this->session->set('basket', $this->session->get('basket') - $order->getQuantity());

        return;
    }

    private function findOrder(Basket $basket, Product $product): Order
    {
        return $this->orderRepository->findOrderProduct($basket, $product);
    }

    private function addQuantity(Order $order)
    {
        $order->setQuantity(null === $order->getQuantity() ? 1: $order->getQuantity() + 1);

        return $order;
    }

    private function substractQuantity(Order $order)
    {
        $order->setQuantity(null === $order->getQuantity() ? 0: $order->getQuantity() - 1);

        return $order;
    }
}
