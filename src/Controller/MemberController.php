<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class MemberController extends AbstractController
{
    /**
     * @Route("/members", name="member_list")
     *
     * @param UserRepository $repository
     *
     * @return Response
     */
    public function index(UserRepository $repository)
    {
        return $this->render('member/index.html.twig', [
            'members' => $repository->findAll(),
        ]);
    }

    /**
     * @Route("/members/{slug}", name="member_show")
     *
     * @param User $user
     *
     * @return Response
     */
    public function show(User $user)
    {
        return $this->render('member/show.html.twig', [
            'member' => $user,
        ]);
    }

    /**
     * @Route("/register", name="register")
     *
     * @param Request                $request
     *
     * @param EntityManagerInterface $manager
     *
     * @return Response
     */
    public function register(
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordEncoderInterface $encoder
    ) {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slugify = new Slugify();
            $user->setSlug($slugify->slugify("{$user->getFirstName()} {$user->getLastName()}"));
            $user->setPicture('https://picsum.photos/200/200');

            $hashed = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hashed);

            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'Votre enregistrement a réussi !');

            return $this->redirectToRoute('home');
        }

        return $this->render('member/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
