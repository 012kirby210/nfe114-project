<?php


namespace App\Controller;


use App\Entity\Conversation;
use App\Form\InvitationSendingFormType;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use App\Repository\ProfileRepository;
use App\Service\InvitationManager\Exception\AlreadySentInvitationException;
use App\Service\InvitationManager\Exception\NotOwnedConversationException;
use App\Service\InvitationManager\Exception\SelfReferencingInvitationException;
use App\Service\InvitationManager\InvitationManagerInterface\InvitationManagerInterface;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;


class InvitationController extends AbstractController
{
    /**
     * @Route("/conversation/{conversationId}/invite",
     *     name="invite_someone_to_join",
     *     requirements={"conversationId"="\d+"})
     * @param Request $request
     * @param InvitationManagerInterface $invitationManager
     * @param UserRepository $userRepository
     * @param ProfileRepository $profileRepository
     * @return JsonResponse
     */
    public function sendInvitation(Request $request,
                                   LoggerInterface $lucLogger,
                                   string $conversationId,
                                   InvitationManagerInterface $invitationManager,
                                   UserRepository $userRepository,
                                   ProfileRepository $profileRepository,
                                   ConversationRepository $conversationRepository):JsonResponse
    {
        $user = $userRepository->findOneBy(['email'=>$this->getUser()->getUserIdentifier()]);
        $hostProfile = $profileRepository->findOneBy(['user' => $user]);
        $conversation = $conversationRepository->findOneBy(['id'=>$conversationId]);
        $invitationSendingForm = $this->createForm(InvitationSendingFormType::class,[]);

        $formError = false;
        $formErrorMessage = '';
        $messageStatusCode = 201;
        if (!$conversation){
            $formErrorMessage = 'La conversation n\'existe pas.';
            $messageStatusCode = 404;
            $formError = true;
        }

        $guestUser = $userRepository->findOneBy(['uuid' => $request->request->get('guest_uuid')]);
        $guestProfile = $profileRepository->findOneBy(['user' => $guestUser]);
        //$lucLogger->debug("profile id : {$guestProfile->getUsername()}");

        if (!$formError && !$guestProfile){
            $formErrorMessage = 'Il n\'y a pas d\'utilisateur avec cet identifiant.';
            $messageStatusCode = 404;
            $formError = true;
        }

        if (!$formError){
            try{
                $invitationManager->send($hostProfile,$guestProfile,$conversation);
            }catch(NotOwnedConversationException $e){
                $formErrorMessage = $e->getMessage();
                $messageStatusCode = 403;
            }catch(AlreadySentInvitationException $e){
                $formErrorMessage = $e->getMessage();
                $messageStatusCode = 400;
            }catch(SelfReferencingInvitationException $e){
                $formErrorMessage = $e->getMessage();
                $messageStatusCode = 403;
            }
        }

        if ($formError){
            $invitationSendingForm->get('guest_uuid')->addError(new FormError($formErrorMessage));
            return new JsonResponse(['html' => $this->renderView('invitations/invitation_form_partials/_invitation_form_input_error.html.twig',
                [   'invitation_sending_form' => $invitationSendingForm->createView() ]
            ), 'error' => $formErrorMessage],$messageStatusCode);
        }

        return new JsonResponse(['html' => $this->renderView('invitations/_form_nouvelle_invitation.html.twig',
            ['invitation_sending_form' => $invitationSendingForm->createView(),
                'conversation'=>$conversation,
                'conversation_id'=>$conversation->getId()
            ]),$messageStatusCode]);
    }

    /**
     * @Route("/testMercure", name="test_mercure")
     * @param Request $request
     * @return Response
     */
    public function testMercureOnPage(Request $request,
                                      HubInterface $hubInterface, LoggerInterface $lucLogger) :Response
    {
        $update = new Update('https://serveur_de_ressource.com/ressource',json_encode(['status'=>'nouveau status']));
        //try{ $hubInterface->publish($update); }catch(\Exception $e){$lucLogger->info("exception message : " . $e->getMessage());}
        $hubInterface->publish($update);

        return $this->render('invitations/test_mercure.html.twig');
    }
}