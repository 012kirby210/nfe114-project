<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Invitation;
use App\Form\EditionConversationFormType;
use App\Form\InvitationSendingFormType;
use App\Form\NouvelleConversationFormType;
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
        $conversationId = 19;
        $hostUser = $userRepository->findOneBy(['email' => $this->getUser()->getUseridentifier()]);
        $hostProfile = $profileRepository->findOneBy(['user' => $hostUser]);
        $invitations = $hostProfile->getSentInvitations();
        dump($invitations);
        $editConversationForm = $this->createForm(EditionConversationFormType::class);
        $editConversationForm->handleRequest($request);
        if ($editConversationForm->isSubmitted()){
            // do something ?
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

    /**
     * @Route("edit_conversation",name="edit_conversation")
     * @param Request $request
     * @return Response
     */
    public function editConversation(Request $request): Response
    {
        // construit l'interface d'édition de la conversation pour l'utilisateur :
        // affiche le titre, le titre est modifiable pour le propriétaire de la conversation.

        // affiche la liste des invitations envoyées par l'utilisateur accédant à la page et les réponses/états.

        // affiche l'interface d'invitation.
    }
}
