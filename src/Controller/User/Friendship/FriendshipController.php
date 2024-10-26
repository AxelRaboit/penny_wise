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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class FriendshipController extends AbstractController
{
    public function __construct(
        private readonly FriendshipRepository $friendshipRepository,
        private readonly FriendshipManager $friendshipManager,
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
            return $this->handleFriendRequestForm($form, $user);
        }

        return $this->render('friendship/index.html.twig', [
            'friendship' => $user->getAcceptedFriends(),
            'form' => $form->createView(),
            'pendingRequests' => $pendingRequests = $this->friendshipRepository->findPendingFriendRequests($user),
            'sentPendingRequests' => $sentPendingRequests = $this->friendshipRepository->findSentPendingRequests($user),
            'pendingRequestsCount' => count($pendingRequests),
            'sentRequests' => $sentRequests = $this->friendshipRepository->findSentFriendRequests($user),
            'sentRequestsCount' => count($sentRequests),
        ]);
    }

    #[Route('/profile/friendship/accept/{id}', name: 'accept_friend_request')]
    #[IsGranted('ROLE_USER')]
    public function acceptFriendRequest(Friendship $friendship): RedirectResponse
    {
        // TODO AXEL: condition below must be in a voter
        if ($friendship->getFriend() !== $this->getUser()) {
            throw $this->createAccessDeniedException("You're not authorized to accept this friend request.");
        }

        $this->friendshipManager->acceptFriendRequest($friendship);

        $this->addFlash('success', 'Friend request accepted.');

        return $this->redirectToRoute('profile_friendship');
    }

    #[Route('/profile/friendship/decline/{id}', name: 'decline_friend_request')]
    #[IsGranted('ROLE_USER')]
    public function declineFriendRequest(Friendship $friendship): RedirectResponse
    {
        if ($friendship->getFriend() !== $this->getUser()) {
            throw $this->createAccessDeniedException("You're not authorized to decline this friend request.");
        }

        $this->friendshipManager->declineFriendRequest($friendship);

        $this->addFlash('warning', 'Friend request declined.');

        return $this->redirectToRoute('profile_friendship');
    }

    #[Route('/profile/friendship/unfriend/{id}', name: 'profile_friendship_unfriend')]
    #[IsGranted('ROLE_USER')]
    #[IsGranted('UNFRIEND', subject: 'friendship')]
    public function unfriend(Friendship $friendship): RedirectResponse
    {
        $this->friendshipManager->unfriend($friendship);

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

        $this->friendshipManager->cancelFriendRequest($friendship);

        $this->addFlash('warning', 'Friend request cancelled.');

        return $this->redirectToRoute('profile_friendship');
    }

    // PRIVATE METHODS

    private function handleFriendRequestForm(FormInterface $form, User $user): RedirectResponse
    {
        /** @var string $usernameOrEmail */
        $usernameOrEmail = $form->get('friend_search')->getData();
        $friend = $this->friendshipManager->findFriendByUsernameOrEmail($usernameOrEmail);

        if ($friend instanceof User) {
            $this->friendshipManager->processFriendRequest($user, $friend);
            $this->addFlash('success', 'Friend request sent.');
        } else {
            $this->addFlash('warning', 'User not found.');
        }

        return $this->redirectToRoute('profile_friendship');
    }
}
