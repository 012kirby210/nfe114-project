<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Form\NouvelleConversationFormType;
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
    public function newConversation(Request $request): Response
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
}
