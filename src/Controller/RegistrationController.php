<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    private $passwordEncoder;
    private $entityManager;
    private $doctrine;

    public function __construct(UserPasswordHasherInterface $passwordEncoder, ManagerRegistry $doctrine)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->doctrine = $doctrine;
    }

    #[Route("/registration", name: "registration")]
    public function index(Request $request)
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encode the new users password
            $user->setPassword($this->passwordEncoder->hashPassword($user, $user->getPassword()));

            // Set their role
            $user->setRoles(['ROLE_USER']);

            // Save
            $em = $this->doctrine->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
