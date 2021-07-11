<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Form\InvitationSendingFormType;
use App\Form\NouvelleConversationFormType;
use App\Repository\ProfileRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConversationController extends AbstractController
{
    /**
     * @Route("/conversation", name="conversation")
     */
    public function index(): Response
    {
        return $this->render('conversation/conversation.html.twig', [
            'controller_name' => 'ConversationController',
        ]);
    }

    /**
     * @Route("/nouvelle_conversation", name="nouvelle_conversation")
     * @param Request $request
     * @return Response
     */
    public function newConversation(Request $request,LoggerInterface $log): Response
    {
        // get the new conversation form and restitute as an ajax response.
        $conversation = new Conversation();
        $nouvelleConversationForm = $this->createForm(NouvelleConversationFormType::class,$conversation);
        /*$nouvelleConversationForm->handleRequest($request);
        if ($nouvelleConversationForm->isSubmitted()){
            $log->info('je suis submitted');
            $nouvelleConversationForm->isValid() && $log->info('je suis validé');
        }else{
            $log->info('Not submited');
        }*/

        $renderedTemplate = $this->render('conversation/nouvelle_conversation.html.twig', [
            'nouvelle_conversation_form' => $nouvelleConversationForm->createView()
        ]);

        return (new JsonResponse([
            'html' => $renderedTemplate->getContent()
        ],200));
    }

    /**
     * Construit une nouvelle conversation, définit son propriétaire et affiche le formulaire d'invitation.
     * @route("creer_conversation",name="creer_conversation")
     * @param Request $request
     * @return Response
     */
    public function createConversation(Request $request,
                                       ProfileRepository $profileRepository,
                                       UserRepository $userRepository,
                                       EntityManagerInterface $em):Response
    {
        $conversation = new Conversation();
        $nouvelleConversationForm = $this->createForm(NouvelleConversationFormType::class,$conversation);
        $nouvelleConversationForm->handleRequest($request);
        if ($nouvelleConversationForm->isSubmitted()){

            $user = $userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);
            $profile = $profileRepository->getProfileByUser($user);
            $conversation->setProprietaire($profile);
            $conversation->setArchived(false);
            $em->persist($conversation);
            $em->flush();

            // les données du formulaire pour une invitation sont un peu compliquées
            $envoiInvitationFormulaire = $this->createForm(InvitationSendingFormType::class);
            // reutilisation du formulaire nouvelle conversation
            $conversationFormulaire = $this->createForm(NouvelleConversationFormType::class,$conversation);
            $invitationFormTemplate = $this->renderView("invitations/form_nouvelle_invitation.html.twig",
                ['invitation_sending_form' => $envoiInvitationFormulaire->createView(),
                    'conversation_form' => $conversationFormulaire->createView(),
                    'conversation' => $conversation]);

            return (new JsonResponse(
                ['html' => $invitationFormTemplate], 200));
        }
        // else
        return (new JsonResponse(['html' => null,500]));
    }

    /**
     * @route("montre_formulaire_invitation",name="montre_formulaire_invitation")
     * @param Request $request
     * @return Response
     */
    public function montreFormulaireDinvitation(LoggerInterface $log,Request $request): Response
    {
        $envoiInvitationFormulaire = $this->createForm(InvitationSendingFormType::class);
        $envoiInvitationFormulaire->handleRequest($request);
        if ($envoiInvitationFormulaire->isSubmitted()){
            $log->info('Le formulaire est envoyé');

        }
        return $this->render("invitations/form_nouvelle_invitation.html.twig", [
           'invitation_sending_form' => $envoiInvitationFormulaire->createView()
        ]);
    }

    /**
     * @Route("inviter_participant",name="inviter_participant")
     * @param Request $request
     * @return Response
     */
    public function inviterParticipant(Request $request,EntityManagerInterface $em):Response
    {
        $invitationForm = $this->createForm(InvitationSendingFormType::class);
        $invitationForm->handleRequest($request);
        if ($invitationForm->isSubmitted()){
            $invitationFormData = $invitationForm->getData();
            $uuid = $invitationFormData['guest_uuid'];
            if (!Uuid::isValid($uuid)){
                return new JsonResponse(['error' => 'Valeur Uuid invalide.'],400);
            }

        }
        return (new Response())->setStatusCode(200);
    }

    /**
     * @Route("update_conversation",name="update_conversation")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function updateConversation(Request $request, EntityManagerInterface $em):Response
    {
        return new Response();
    }

}
