<?php

declare(strict_types=1);

namespace App\Controller\Messenger;

use App\Entity\Messenger;
use App\Entity\MessengerMessage;
use App\Entity\MessengerParticipant;
use App\Entity\MessengerTalk;
use App\Entity\User;
use App\Repository\Messenger\MessengerParticipantRepository;
use App\Repository\Messenger\MessengerTalkRepository;
use App\Repository\Profile\UserRepository;
use App\Repository\User\Friendship\FriendshipRepository;
use App\Service\User\UserCheckerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MessengerController extends AbstractController
{
    public function __construct(
        private readonly MessengerTalkRepository $messengerTalkRepository,
        private readonly UserRepository $userRepository,
        private readonly FriendshipRepository $friendshipRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserCheckerService $userCheckerService
    ) {}

    #[Route('/messages', name: 'messenger_list')]
    #[IsGranted('ROLE_USER')]
    public function list(): Response
    {
        $user = $this->userCheckerService->getUserOrThrow();
        $messenger = $user->getMessenger();
        $messengerId = $messenger->getId();
        if (!$messengerId) {
            throw $this->createNotFoundException('Messenger not found');
        }

        $talks = $this->messengerTalkRepository->findTalksByUser($messengerId);

        return $this->render('messenger/list/list.html.twig', [
            'talks' => $talks,
        ]);
    }

    #[Route('/messages/t/{id}', name: 'messenger_talk_view')]
    #[IsGranted('ROLE_USER')]
    public function viewTalk(int $id): Response
    {
        $talk = $this->messengerTalkRepository->find($id);
        if (!$talk) {
            throw $this->createNotFoundException('Conversation not found');
        }

        $user = $this->userCheckerService->getUserOrThrow();
        $messenger = $user->getMessenger();
        $messengerId = $messenger->getId();
        if (!$messengerId) {
            throw $this->createNotFoundException('Messenger not found');
        }

        $participant = null;
        foreach ($talk->getParticipants() as $p) {
            if ($p->getMessenger()->getUser() !== $user) {
                $participant = $p;
                break;
            }
        }

        $talks = $this->messengerTalkRepository->findTalksByUser($messengerId);

        return $this->render('messenger/talk/view/talk.html.twig', [
            'talk' => $talk,
            'talks' => $talks,
            'messages' => $talk->getMessages(),
            'participant' => $participant
        ]);
    }

    #[Route('/messenger/new-talk/{friendId}', name: 'messenger_new_talk')]
    #[IsGranted('ROLE_USER')]
    public function newTalk(int $friendId): Response
    {
        $user = $this->userCheckerService->getUserOrThrow();

        $friend = $this->userRepository->find($friendId);
        if (!$friend || !$this->friendshipRepository->isFriend($user, $friend)) {
            throw $this->createNotFoundException('Friend not found or not a friend.');
        }

        $existingTalk = $this->messengerTalkRepository->findExistingTalk($user, $friend);
        if ($existingTalk) {
            return $this->redirectToRoute('messenger_talk_view', ['id' => $existingTalk->getId()]);
        }

        $talk = new MessengerTalk();

        $currentMessenger = $user->getMessenger();
        $friendMessenger = $friend->getMessenger();

        $currentUserParticipant = new MessengerParticipant();
        $currentUserParticipant->setMessenger($currentMessenger)->setTalk($talk);

        $friendParticipant = new MessengerParticipant();
        $friendParticipant->setMessenger($friendMessenger)->setTalk($talk);

        $talk->addParticipant($currentUserParticipant);
        $talk->addParticipant($friendParticipant);

        // Save everything
        $this->entityManager->persist($talk);
        $this->entityManager->persist($currentUserParticipant);
        $this->entityManager->persist($friendParticipant);
        $this->entityManager->flush();

        return $this->redirectToRoute('messenger_talk_view', ['id' => $talk->getId()]);
    }

    #[Route('/messenger/new-talk', name: 'messenger_new_talk_list')]
    #[IsGranted('ROLE_USER')]
    public function newTalkList(): Response
    {
        $user = $this->userCheckerService->getUserOrThrow();

        $friends = $this->friendshipRepository->findAcceptedFriendships($user);
        $friendsWithoutTalk = [];

        foreach ($friends as $friendship) {
            $friend = $friendship->getRequester() === $user ? $friendship->getFriend() : $friendship->getRequester();
            if (!$this->messengerTalkRepository->findExistingTalk($user, $friend)) {
                $friendsWithoutTalk[] = $friend;
            }
        }

        return $this->render('messenger/talk/new_talk_list.html.twig', [
            'friendsWithoutTalk' => $friendsWithoutTalk,
        ]);
    }

    #[Route('/messenger/talk/{talkId}/send', name: 'messenger_message_send', methods: ['POST'])]
    public function sendMessage(int $talkId, Request $request): Response
    {
        $messageContent = $request->request->get('message');
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $talk = $this->messengerTalkRepository->find($talkId);

        if (!$talk) {
            throw $this->createNotFoundException('Conversation not found');
        }

        $message = new MessengerMessage();
        $message->setContent($messageContent)
            ->setSender($currentUser)
            ->setTalk($talk);

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $this->redirectToRoute('messenger_talk_view', ['id' => $talkId]);
    }
}
