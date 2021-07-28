<?php

namespace App\Controller;

use App\Entity\Profile;
use App\Entity\User;
use App\Form\ProfileFormType;

use App\Repository\ProfileRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profile", name="app_profile")
     */
    public function index(EntityManagerInterface $em,
                          ProfileRepository $profileRepository,
                          UserRepository $userRepository,
                          Request $request): Response
    {
        $user = $userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);
        $profile = $profileRepository->getProfileByUser($user);
        $profile = $profile?? (new Profile())->setUser($user);
        $form = $this->createForm(ProfileFormType::class,$profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $oldProfile = $profile;
            /** @var Profile $profile */
            $profile = $form->getData();
            $profile->setNotificationDesktop((bool) $oldProfile->getNotificationDesktop());
            $profile->setNotificationEmail((bool) $oldProfile->getNotificationEmail());
            $em->persist($profile);
            $em->flush();
        }

        return $this->render('profile/profile_home.html.twig', [
            'controller_name' => 'ProfileController',
            'profile_form' => $form->createView(),
            'picture' => $profile->getPicture(),
            'uuid' => $user->getUuid(),
            'csrf_token_string' => 'tokenString'
        ]);
    }

    /**
     * @Route("/upload_profile_picture", name="upload_profile_picture")
     * @param Request $request
     * @return Response
     */
    public function uploadFile(EntityManagerInterface $em, LoggerInterface $logger,Request $request):Response
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('file');
        $destination = $this->getParameter('kernel.project_dir').'/public/uploads';
        try {
            // !TODO le contrôleur fait trop de choses
            $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $newFileName = $originalFileName.'-'.uniqid().'.'.$file->guessExtension();
            $file->move($destination,$newFileName);

            // we then persist the profil picture
            $userRepository = $em->getRepository(User::class);
            /** @var User $user */
            $user = $userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);
            /** @var ProfileRepository $profileRepository */
            $profileRepository = $em->getRepository(Profile::class);
            /** @var Profile $profile */
            $profile = $profileRepository->getProfileByUser($user);
            $profile->setPicture($newFileName);
            $em->persist($profile);
            $em->flush();

            // we should send back ajax with template for the replacement.
        }catch(FileException $e){
            $logger->debug("The profile picture could not be moved." .$e->getMessage());
        }
        $response = new Response();$response->setStatusCode(200);
        return $response;
    }

}
