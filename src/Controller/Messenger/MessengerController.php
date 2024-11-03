<?php

declare(strict_types=1);

namespace App\Controller\Messenger;

use App\Entity\MessengerTalk;
use App\Entity\User;
use App\Form\Messenger\MessengerMessageSendType;
use App\Manager\Messenger\MessengerManager;
use App\Repository\Profile\UserRepository;
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
        private readonly UserCheckerService $userCheckerService,
        private readonly UserRepository $userRepository
    ) {}

    #[Route('/messages', name: 'messenger_list', methods: ['GET'])]
    public function list(): Response
    {
        $user = $this->userCheckerService->getUserOrThrow();
        $talks = $this->messengerManager->getTalksForUser($user);

        // Récupérer la liste des amis pour une nouvelle conversation
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
            $this->messengerManager->addMessage($talk, $user, $content);

            return $this->redirectToRoute('messenger_talk_view', ['id' => $talk->getId()]);
        }

        // Récupérer la liste des amis
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


    #[Route('/messenger/talk/{id}/send', name: 'messenger_message_send', methods: ['POST'])]
    #[IsGranted(MessengerVoter::SEND_MESSAGE, subject: 'talk')]
    public function sendMessage(MessengerTalk $talk, Request $request): Response
    {
        $content = (string) $request->request->get('message');
        $user = $this->userCheckerService->getUserOrThrow();

        $this->messengerManager->addMessage($talk, $user, $content);

        return $this->redirectToRoute('messenger_talk_view', ['id' => $talk->getId()]);
    }

    #[Route('/messages/new/{friendId}', name: 'messenger_create_talk')]
    #[IsGranted('ROLE_USER')]
    public function createTalk(int $friendId): Response
    {
        $user = $this->userCheckerService->getUserOrThrow();
        $friend = $this->userRepository->find($friendId);

        if (!$friend) {
            throw $this->createNotFoundException('Friend not found');
        }

        // Créez un nouveau talk avec l'ami sélectionné
        $talk = $this->messengerManager->createTalk($user, $friend);

        return $this->redirectToRoute('messenger_talk_view', ['id' => $talk->getId()]);
    }

    #[Route('/messages/new', name: 'messenger_new_conversation')]
    #[IsGranted('ROLE_USER')]
    public function newConversation(): Response
    {
        $user = $this->userCheckerService->getUserOrThrow();
        $friends = $this->messengerManager->getFriendsForNewConversation($user);

        return $this->render('messenger/talk/new_conversation.html.twig', [
            'friends' => $friends,
        ]);
    }


}
