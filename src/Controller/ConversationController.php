<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Invitation;
use App\Form\EditionConversationFormType;
use App\Form\InvitationSendingFormType;
use App\Form\NouvelleConversationFormType;
use App\Repository\ConversationRepository;
use App\Repository\InvitationRepository;
use App\Repository\ProfileRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Form\FormInterface;

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
     * @Route("/mmmIndex", name="mmm_index")
     * @param Request $request
     * @return Response
     */
    public function mmmIndex(Request $request,
                             UserRepository $userRepository,
                             ProfileRepository $profileRepository): Response
    {
        $interaction_instance = 'conversation/__edit_conversation.html.twig';
        $user = $userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);
        $profile = $profileRepository->findOneBy(['user' => $user]);

        $conversations = $profile->getOwnedConversations();
        $conversation = $conversations[0];
        $invitations = $profile->getSentInvitations();

        $editConversationForm = $this->createForm(EditionConversationFormType::class,['titre'=>$conversation->getTitre()]);
        $editConversationForm->handleRequest($request);

        return $this->render('conversation/_index_conversation.html.twig',
            [
                'profile' => $profile,
                'interaction_instance' => $interaction_instance,
                'edition_conversation_form' => $editConversationForm->createView(),
                'invitations' => $invitations
                ]);
    }

    /**
     * @Route("/conversation/edit/{conversationId<\d+>}"),
     * @param Request $request
     * @return Response
     */
    public function editConversation(Request $request,
                                     UserRepository $userRepository,
                                     ProfileRepository $profileRepository,
                                     ConversationRepository $conversationRepository,
                                     string $conversationId):Response
    {
        $interaction_instance = 'conversation/__edit_conversation.html.twig';
        $user = $userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);
        $profile = $profileRepository->findOneBy(['user' => $user]);
        $conversation = $conversationRepository->findOneBy(['id' => $conversationId]);
        if (!$conversation->getParticipants()->contains($profile))
        {
            // the user has nothing to do here

        }

        //$conversations = $profile->getOwnedConversations();
        //$conversation = $conversations[0];
        $invitations = $profile->getSentInvitations();
        // enrichissement des invitations de la couche présentation
        // !TODO passer en clean code par injection
        foreach($invitations as &$invitation){
            $nomEtat = 'En attente';
            switch($invitation->getEtat()){
                case 'canceled':
                    $nomEtat = 'Déclinée';
                    break;
                case 'accepted':
                    $nomEtat = 'Acceptée';
                    break;
                default:
                    break;
            }
            // surchage magique __set
            $invitation->nomEtat = $nomEtat;
        }
        unset($invitation);

        $editConversationForm = $this->createForm(EditionConversationFormType::class,['titre'=>$conversation->getTitre()]);
        $editConversationForm->handleRequest($request);

        return $this->render('conversation/___edit_conversation.html.twig',
            [
                'profile' => $profile,
                'interaction_instance' => $interaction_instance,
                'edition_conversation_form' => $editConversationForm->createView(),
                'invitations' => $invitations,
                'conversation_id' => $conversation->getId()
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

            // renvoyer le formulaire d'invitation + le nouvel url à pousser dans l'historique
            // pour qu'au rechargement de la page par le client, l'état soit conservé.
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
        $envoiInvitationFormulaire->get('guest_uuid')->addError(new FormError('je suis une erreur'));
        return $this->render("invitations/_form_nouvelle_invitation.html.twig", [
           'invitation_sending_form' => $envoiInvitationFormulaire->createView(),
        ]);
    }

    /**
     * @Route("montre_formulaire_edition_conversation",name="montre_formulaire_edition_conversation")
     * @param Request $request
     * @param InvitationRepository $invitationRepository
     * @return Response
     */
    public function montreFormulaireEditConversation(Request $request,
                                                     UserRepository $userRepository,
                                                     ProfileRepository $profileRepository):Response
    {
        // the conversation Id should be specified in the request :
        $hostUser = $userRepository->findOneBy(['email' => $this->getUser()->getUseridentifier()]);
        $hostProfile = $profileRepository->findOneBy(['user' => $hostUser]);
        $conversations = $hostProfile->getOwnedConversations();
        $conversation = $conversations[0];
        $invitations = $hostProfile->getSentInvitations();
        $editConversationForm = $this->createForm(EditionConversationFormType::class,['titre'=>$conversation->getTitre()]);
        $editConversationForm->handleRequest($request);
        if ($editConversationForm->isSubmitted()){

        }
        return $this->render("conversation/_edit_conversation.html.twig",
            [
                'edition_conversation_form' => $editConversationForm->createView(),
                'invitations' => $invitations
            ]);
    }

    /**
     * @Route("inviter_participant",name="inviter_participant")
     * @param Request $request
     * @return Response
     */
    public function inviterParticipant(Request $request,
                                       EntityManagerInterface $em,
                                       UserRepository $userRepository,
                                       ProfileRepository $profileRepository,
                                       InvitationRepository $invitationRepository,
                                       LoggerInterface $lucLogger):Response
    {
        $invitationForm = $this->createForm(InvitationSendingFormType::class);
        $invitationForm->handleRequest($request);
        if ($invitationForm->isSubmitted()){
            if(count($invitationForm['guest_uuid']->getErrors()) !== 0){
                $renderedTemplate = $this->renderView("invitations/partial_form_nouvelle_invitation.html.twig",
                ['invitation_sending_form' => $invitationForm->createView()]);
                return new JsonResponse(['html' => $renderedTemplate, 'error' => $invitationForm['guest_uuid']->getErrors()],400);
            }

            $invitationFormData = $invitationForm->getData();
            $uuid = strtolower($invitationFormData['guest_uuid']);
            $user = $userRepository->findOneBy(['uuid' => $uuid]);

            if (!$user){
                $invitationForm->get('guest_uuid')->addError(new FormError('Utilisateur non trouvé'));
                $renderedTemplate = $this->renderView("invitations/partial_form_nouvelle_invitation.html.twig",
                    ['invitation_sending_form' => $invitationForm->createView()]);
                return new JsonResponse(['html' => $renderedTemplate,'error' => 'Utilisateur non trouvé.'],404);
            }

            // user is found => we make an invite.
            $invitation = new Invitation();
            $hostUser = $userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);
            $hostProfile = $profileRepository->getProfileByUser($hostUser);
            $guestProfile = $profileRepository->getProfileByUser($user);
            $invitation->setGuest($guestProfile)->setHost($hostProfile);
            $em->persist($invitation);
            $em->flush();

            // build the list of sent invitation
            $invitations = $invitationRepository->getSentInvitationsBy($hostProfile);

            // build the invitation sending form
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
        //
        return new Response();
    }

}
