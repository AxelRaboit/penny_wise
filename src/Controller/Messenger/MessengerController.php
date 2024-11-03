<?php

declare(strict_types=1);

namespace App\Controller\Messenger;

use App\Entity\MessengerTalk;
use App\Form\Messenger\MessengerMessageSendType;
use App\Manager\Messenger\MessengerManager;
use App\Repository\Profile\UserRepository;
use App\Service\User\UserCheckerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Turbo\TurboBundle;

class MessengerController extends AbstractController
{
    public function __construct(
        private readonly MessengerManager $messengerManager,
        private readonly UserCheckerService $userCheckerService,
        private readonly UserRepository $userRepository
    ) {}

    #[Route('/messages', name: 'messenger_list', methods: ['GET'])]
    public function list(): Response
    {
        $user = $this->userCheckerService->getUserOrThrow();
        $talks = $this->messengerManager->getTalksForUser($user);

        $friends = $this->messengerManager->getFriendsForNewConversation($user);

        return $this->render('messenger/list/list.html.twig', [
            'talks' => $talks,
            'friends' => $friends,
        ]);
    }

    #[Route('/messages/t/{id}', name: 'messenger_talk_view')]
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

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                $message = $this->messengerManager->addMessage($talk, $user, $content);
                return $this->render('messenger/talk/view/message.stream.html.twig', [
                    'message' => $message,
                ]);
            }
        }

        $friends = $this->messengerManager->getFriendsForNewConversation($user);

        return $this->render('messenger/talk/view/talk.html.twig', [
            'currentTalk' => $talk,
            'talks' => $talks,
            'messages' => $talk->getMessages(),
            'participant' => $participant,
            'form' => $form->createView(),
            'friends' => $friends,
        ]);
    }

    #[Route('/messages/new/{id}', name: 'messenger_create_talk')]
    #[IsGranted('ROLE_USER')]
    public function createTalk(int $id): Response
    {
        $user = $this->userCheckerService->getUserOrThrow();
        $friend = $this->userRepository->find($id);

        if (null === $friend) {
            throw $this->createNotFoundException('Friend not found');
        }

        $talk = $this->messengerManager->createOrReopenTalk($user, $friend);

        return $this->redirectToRoute('messenger_talk_view', ['id' => $talk->getId()]);
    }

    #[Route('/messages/hide/{id}', name: 'messenger_talk_hide')]
    public function hideTalk(MessengerTalk $talk): Response
    {
        $user = $this->userCheckerService->getUserOrThrow();
        $this->messengerManager->hideTalkForUser($talk, $user);

        return $this->redirectToRoute('messenger_list');
    }
}
