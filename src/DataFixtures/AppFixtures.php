<?php

namespace App\DataFixtures;

use App\Entity\Invitation;
use App\Entity\Profile;
use App\Entity\User;
use App\Entity\Conversation;
use App\Repository\ProfileRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    private $profileRepository;
    private $userRepository;

    public function __construct(ProfileRepository $profileRepository, UserRepository $userRepository)
    {
        $this->profileRepository = $profileRepository;
        $this->userRepository = $userRepository;
    }

    public function load(ObjectManager $manager)
    {
        $this->loadUsers($manager);
        $this->loadProfile($manager);
        $this->loadConversation($manager);
        $this->loadInvitations($manager);
    }

    private function loadUsers(ObjectManager $manager)
    {
        $user1 = User::create();
        $user1->setPassword('$2y$13$Y5uUr8LVDgAaBF1wD6WSGOArO6KK.W7SBqfuvNuVo4uodJp02Nt5G');
        $user1->setEmail('luc.nouailhaguet@gmail.com');
        $user1->setIsVerified(1);
        $manager->persist($user1);

        $user2 = User::create();
        $user2->setPassword('$argon2id$v=19$m=65536,t=4,p=1$m3DJrVF1DfNva5j7J+Ba2Q$aFVyzSwUIwJpH0VR10hexaORpRqws+fA+MRKzzFMDQw');
        $user2->setEmail('other.user@mail.com');
        $user2->setIsVerified(1);
        $manager->persist($user2);

        $manager->flush();
    }

    private function loadProfile(ObjectManager $manager)
    {
        $user1 = $this->userRepository->findOneBy(['email' => 'luc.nouailhaguet@gmail.com']);

        $profile1 = new Profile();
        $profile1->setPicture('pexels-neosiam-601798-60c650756e577.jpg');
        $profile1->setUsername('Zoé');
        $profile1->setFirstName('Luc');
        $profile1->setLastname('Nouailhaguet');
        $profile1->setNotificationEmail(0)->setNotificationDesktop(0);
        $profile1->setUser($user1);
        $manager->persist($profile1);

        $user2 = $this->userRepository->findOneBy(['email' => 'other.user@mail.com']);

        $profile2 = new Profile();
        $profile2->setPicture('pexels-philippe-donn-1114690-60c787063e698.jpg');
        $profile2->setUsername('user_name');
        $profile2->setFirstName('Prenom');
        $profile2->setLastName('Nom');
        $profile2->setNotificationEmail(0)->setNotificationDesktop(0);
        $profile2->setUser($user2);
        $manager->persist($profile2);

        $manager->flush();
    }

    private function loadConversation(ObjectManager $manager)
    {
        $user1 = $this->userRepository->findOneBy(['email' => 'luc.nouailhaguet@gmail.com']);

        $profile1 = $this->profileRepository->findOneBy(['user' => $user1]);

        $conversation = new Conversation();
        $conversation->setArchived(false);
        $conversation->setProprietaire($profile1);
        $conversation->setTitre('le titre de la conversation');
        $manager->persist($conversation);

        $manager->flush();
    }

    private function loadInvitations(ObjectManager $manager)
    {
        $user1 = $this->userRepository->findOneBy(['email' => 'luc.nouailhaguet@gmail.com']);
        $user2 = $this->userRepository->findOneBy(['email' => 'other.user@mail.com']);

        $profile1 = $this->profileRepository->findOneBy(['user' => $user1]);
        $profile2 = $this->profileRepository->findOneBy(['user' => $user2]);

        $conversations = $profile1->getOwnedConversations();

        $conversation = $conversations[0];

        $invitationStates = ['pending','canceled','accepted'];
        foreach ($invitationStates as $invitationState){
            $invitation = new Invitation();
            $invitation->setGuest($profile2);
            $invitation->setHost($profile1);
            $invitation->setUpdateDatetime(date('Y-m-d H:i:s',time()));
            $invitation->setEtat($invitationState);
            $invitation->setConversation($conversation);
            $manager->persist($invitation);
        }

        $invitation = new Invitation();
        $invitation->setGuest($profile1);
        $invitation->setHost($profile2);
        $invitation->setUpdateDatetime(date('Y-m-d H:i:s',time()));
        $invitation->setEtat($invitationState);
        $invitation->setConversation($conversation);
        $manager->persist($invitation);


        $manager->flush();
    }
}
