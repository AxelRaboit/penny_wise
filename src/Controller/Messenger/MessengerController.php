<?php

declare(strict_types=1);

namespace App\Controller\Messenger;

use App\Entity\MessengerTalk;
use App\Form\Messenger\MessengerMessageSendType;
use App\Manager\Messenger\MessengerManager;
use App\Security\Voter\Messenger\MessengerVoter;
use App\Service\User\UserCheckerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MessengerController extends AbstractController
{
    public function __construct(
        private readonly MessengerManager $messengerManager,
        private readonly UserCheckerService $userCheckerService
    ) {}

    #[Route('/messages', name: 'messenger_list')]
    #[IsGranted('ROLE_USER')]
    public function list(): Response
    {
        $user = $this->userCheckerService->getUserOrThrow();
        $talks = $this->messengerManager->getTalksForUser($user);

        return $this->render('messenger/list/list.html.twig', [
            'talks' => $talks,
        ]);
    }

    #[Route('/messages/t/{id}', name: 'messenger_talk_view')]
    #[IsGranted(MessengerVoter::VIEW, subject: 'talk')]
    public function viewTalk(MessengerTalk $talk, Request $request): Response
    {
        $user = $this->userCheckerService->getUserOrThrow();
        $talks = $this->messengerManager->getTalksForUser($user);
        $participant = $this->messengerManager->getTalkParticipant($talk, $user);

        $form = $this->createForm(MessengerMessageSendType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $content */
            $content = $form->get('message')->getData();
            $this->messengerManager->addMessage($talk, $user, $content);

            return $this->redirectToRoute('messenger_talk_view', ['id' => $talk->getId()]);
        }

        return $this->render('messenger/talk/view/talk.html.twig', [
            'currentTalk' => $talk,
            'talks' => $talks,
            'messages' => $talk->getMessages(),
            'participant' => $participant,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/messenger/talk/{id}/send', name: 'messenger_message_send', methods: ['POST'])]
    #[IsGranted(MessengerVoter::SEND_MESSAGE, subject: 'talk')]
    public function sendMessage(MessengerTalk $talk, Request $request): Response
    {
        $content = (string) $request->request->get('message');
        $user = $this->userCheckerService->getUserOrThrow();

        $this->messengerManager->addMessage($talk, $user, $content);

        return $this->redirectToRoute('messenger_talk_view', ['id' => $talk->getId()]);
    }
}
