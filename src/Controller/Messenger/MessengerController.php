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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MessengerController extends AbstractController
{
    public function __construct(
        private readonly MessengerParticipantRepository $messengerParticipantRepository,
        private readonly MessengerTalkRepository $messengerTalkRepository,
        private readonly UserRepository $userRepository,
        private readonly FriendshipRepository $friendshipRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('/messenger', name: 'messenger_list')]
    public function list(): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        /** @var Messenger|null $messenger */
        $messenger = $currentUser->getMessenger();

        // Fetch talks directly and rename variable for clarity
        $talks = $this->messengerTalkRepository->findTalksByUser($messenger->getId());

        return $this->render('messenger/list/list.html.twig', [
            'talks' => $talks,
        ]);
    }


    #[Route('/messenger/talk/{id}', name: 'messenger_talk_view')]
    public function viewTalk(int $id): Response
    {
        $talk = $this->messengerTalkRepository->find($id);

        if (!$talk) {
            throw $this->createNotFoundException('Conversation not found');
        }

        // $this->denyAccessUnlessGranted('VIEW', $talk);

        return $this->render('messenger/talk/talk.html.twig', [
            'talk' => $talk,
            'messages' => $talk->getMessages(),
        ]);
    }

    #[Route('/messenger/new-talk/{friendId}', name: 'messenger_new_talk')]
    public function newTalk(int $friendId): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        // Ensure both users are friends
        $friend = $this->userRepository->find($friendId);
        if (!$friend || !$this->friendshipRepository->isFriend($currentUser, $friend)) {
            throw $this->createNotFoundException('Friend not found or not a friend.');
        }

        // Check if a talk already exists between the two users
        $existingTalk = $this->messengerTalkRepository->findExistingTalk($currentUser, $friend);
        if ($existingTalk) {
            return $this->redirectToRoute('messenger_talk_view', ['id' => $existingTalk->getId()]);
        }

        // Create a new talk and participants
        $talk = new MessengerTalk();

        $currentMessenger = $currentUser->getMessenger();
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
    public function newTalkList(): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        // Retrieve the user's accepted friends
        $friends = $this->friendshipRepository->findAcceptedFriendships($currentUser);
        $friendsWithoutTalk = [];

        // Check if there is an existing talk with each friend
        foreach ($friends as $friendship) {
            $friend = $friendship->getRequester() === $currentUser ? $friendship->getFriend() : $friendship->getRequester();
            if (!$this->messengerTalkRepository->findExistingTalk($currentUser, $friend)) {
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

        // Find the talk by ID
        $talk = $this->messengerTalkRepository->find($talkId);

        if (!$talk) {
            throw $this->createNotFoundException('Conversation not found');
        }

        // Create a new message
        $message = new MessengerMessage();
        $message->setContent($messageContent)
            ->setSender($currentUser)
            ->setTalk($talk);

        // Persist the new message
        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $this->redirectToRoute('messenger_talk_view', ['id' => $talkId]);
    }



}
