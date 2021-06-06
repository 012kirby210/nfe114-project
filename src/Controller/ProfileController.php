<?php

namespace App\Controller;

use App\Entity\Profile;
use App\Entity\User;
use App\Form\ProfileFormType;

use App\Repository\ProfileRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profile", name="app_profile")
     */
    public function index(EntityManagerInterface $em,ProfileRepository $profileRepository,UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);
        $profile = $profileRepository->getProfileByUser($user);

        $profile = $profile?? (new Profile())->setUser($user);

        $form = $this->createForm(ProfileFormType::class,$profile);

        return $this->render('profile/profile_home.html.twig', [
            'controller_name' => 'ProfileController',
            'profile_form' => $form->createView()
        ]);
    }
}
