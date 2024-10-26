<?php

declare(strict_types=1);

namespace App\Controller\User\Friendship;

use App\Entity\Friendship;
use App\Entity\User;
use App\Form\User\FriendShip\AddFriendType;
use App\Manager\User\Friendship\FriendshipManager;
use App\Repository\Profile\UserRepository;
use App\Repository\User\Friendship\FriendshipRepository;
use App\Service\User\UserCheckerService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class FriendshipController extends AbstractController
{
    public function __construct(
        private readonly FriendshipRepository $friendshipRepository,
        private readonly FriendshipManager $friendshipService,
        private readonly UserCheckerService $userCheckerService,
        private readonly UserRepository $userRepository,
        private readonly LoggerInterface $logger
    ) {}

    #[Route('/profile/friendship', name: 'profile_friendship')]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request): Response
    {
        $user = $this->userCheckerService->getUserOrThrow();
        $form = $this->createForm(AddFriendType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $friend */
            $friend = $form->get('username')->getData();

            if ([] === $this->friendshipRepository->findPendingFriendRequests($friend)) {
                $this->friendshipService->sendFriendRequest($user, $friend);
                $this->addFlash('success', 'Friend request sent.');
            } else {
                $this->addFlash('warning', 'You are already friends or request already sent.');
            }

            return $this->redirectToRoute('profile_friendship');
        }

        $pendingRequests = $this->friendshipRepository->findPendingFriendRequests($user);
        $sentPendingRequests = $this->friendshipRepository->findSentPendingRequests($user);
        $pendingRequestsCount = count($pendingRequests);

        $sentRequests = $this->friendshipRepository->findSentFriendRequests($user);
        $sentRequestsCount = count($sentRequests);

        return $this->render('friendship/index.html.twig', [
            'friendship' => $user->getAcceptedFriends(),
            'addFriendForm' => $form->createView(),
            'pendingRequests' => $pendingRequests,
            'sentPendingRequests' => $sentPendingRequests,
            'pendingRequestsCount' => $pendingRequestsCount,
            'sentRequests' => $sentRequests,
            'sentRequestsCount' => $sentRequestsCount,
        ]);
    }

    #[Route('/profile/friendship/accept/{id}', name: 'accept_friend_request', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function acceptFriendRequest(Friendship $friendship): RedirectResponse
    {
        if ($friendship->getFriend() !== $this->getUser()) {
            throw $this->createAccessDeniedException("You're not authorized to accept this friend request.");
        }

        $this->friendshipService->acceptFriendRequest($friendship);

        $this->addFlash('success', 'Friend request accepted.');

        return $this->redirectToRoute('profile_friendship');
    }

    #[Route('/profile/friendship/decline/{id}', name: 'decline_friend_request', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function declineFriendRequest(Friendship $friendship): RedirectResponse
    {
        if ($friendship->getFriend() !== $this->getUser()) {
            throw $this->createAccessDeniedException("You're not authorized to decline this friend request.");
        }

        $this->friendshipService->declineFriendRequest($friendship);

        $this->addFlash('info', 'Friend request declined.');

        return $this->redirectToRoute('profile_friendship');
    }

    #[Route('/profile/friendship/unfriend/{id}', name: 'unfriend')]
    #[IsGranted('ROLE_USER')]
    public function unfriend(Friendship $friendship): RedirectResponse
    {
        $user = $this->getUser();

        if ($friendship->getRequester() !== $user && $friendship->getFriend() !== $user) {
            throw $this->createAccessDeniedException('You are not authorized to unfriend this user.');
        }

        $this->friendshipService->unfriend($friendship);

        $this->addFlash('success', 'Friendship removed successfully.');

        return $this->redirectToRoute('profile_friendship');
    }

    #[Route('/profile/friendship/view/{username}', name: 'profile_view')]
    #[IsGranted('ROLE_USER')]
    public function viewProfile(string $username): Response
    {
        $userProfile = $this->userRepository->findOneBy(['username' => $username]);
        if (null === $userProfile) {
            $this->logger->error('User not found.', [
                'username' => $username,
            ]);

            return $this->redirectToRoute('profile_friendship');
        }

        $this->denyAccessUnlessGranted('VIEW_PROFILE', $userProfile);

        $user = $this->userCheckerService->getUserOrThrow();
        $friendship = $this->friendshipRepository->findFriendshipDtoBetweenUsers($user, $userProfile);

        return $this->render('friendship/view.html.twig', [
            'userProfile' => $userProfile,
            'friendship' => $friendship,
        ]);
    }

    #[Route('/profile/friendship/cancel/{id}', name: 'cancel_friend_request')]
    #[IsGranted('ROLE_USER')]
    public function cancelFriendRequest(Friendship $friendship): RedirectResponse
    {
        $user = $this->getUser();

        if ($friendship->getRequester() !== $user) {
            throw $this->createAccessDeniedException("You're not authorized to cancel this friend request.");
        }

        $this->friendshipService->cancelFriendRequest($friendship);

        $this->addFlash('info', 'Friend request cancelled.');

        return $this->redirectToRoute('profile_friendship');
    }
}
