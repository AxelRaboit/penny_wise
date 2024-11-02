<?php

declare(strict_types=1);

namespace App\Controller\Messenger;

use App\Service\Messenger\MessengerService;
use App\Manager\Messenger\MessengerManager;
use App\Service\User\UserCheckerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MessengerController extends AbstractController
{
    public function __construct(
        private readonly MessengerService $messengerService,
        private readonly MessengerManager $messengerManager,
        private readonly UserCheckerService $userCheckerService
    ) {}

    #[Route('/messages', name: 'messenger_list')]
    #[IsGranted('ROLE_USER')]
    public function list(): Response
    {
        $user = $this->userCheckerService->getUserOrThrow();
        $talks = $this->messengerService->getTalksForUser($user);

        return $this->render('messenger/list/list.html.twig', [
            'talks' => $talks,
        ]);
    }

    #[Route('/messages/t/{id}', name: 'messenger_talk_view')]
    #[IsGranted('ROLE_USER')]
    public function viewTalk(int $id): Response
    {
        $user = $this->userCheckerService->getUserOrThrow();
        $talk = $this->messengerService->findTalkById($id);
        if (!$talk) {
            throw $this->createNotFoundException('Conversation not found');
        }

        $talks = $this->messengerService->getTalksForUser($user);
        $participant = $this->messengerService->getTalkParticipant($talk, $user);

        return $this->render('messenger/talk/view/talk.html.twig', [
            'talk' => $talk,
            'talks' => $talks,
            'messages' => $talk->getMessages(),
            'participant' => $participant,
        ]);
    }

    #[Route('/messenger/talk/{talkId}/send', name: 'messenger_message_send', methods: ['POST'])]
    public function sendMessage(int $talkId, Request $request): Response
    {
        /** @var string $content */
        $content = $request->request->get('message');
        $user = $this->userCheckerService->getUserOrThrow();

        $talk = $this->messengerService->findTalkById($talkId);

        if (!$talk) {
            throw $this->createNotFoundException('Conversation not found');
        }

        $this->messengerManager->addMessage($talk, $user, $content);

        return $this->redirectToRoute('messenger_talk_view', ['id' => $talkId]);
    }
}

