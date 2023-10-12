<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use App\Service\OpenAIService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;


class ChatController extends AbstractController
{
    #[Route('/', name: 'app_chat')]
    public function chat(): Response
    {
        // $message = new Message();
        // $form = $this->createForm(MessageType::class, $message);
        return $this->render('chat/index.html.twig', [
        ]);
    }


    #[Route('/api/gpt', name: 'api_pgt', methods: ['GET', 'POST'])]
    public function gpt(Session $session, Request $request, EntityManagerInterface $entityManager, OpenAIService $openAIService): Response
    {
        $openAIService->setSystemMessage('Parle comme une racaille de quartier, soit agressif');

        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $response = $openAIService->generateResponse($form->get('content')->getData());
            $message->setCreatedAt(new DateTimeImmutable())
                ->setRole('user');

            $entityManager->persist($message);
            $entityManager->persist($response);
            $entityManager->flush();
        }

        $chat = $openAIService->getArrayForJsonEncode($form);

        return new JsonResponse($chat);
    }
}