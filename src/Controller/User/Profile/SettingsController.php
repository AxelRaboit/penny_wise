<?php

declare(strict_types=1);

namespace App\Controller\User\Profile;

use App\Form\User\Profile\ProfileType;
use App\Manager\User\Profile\Settings\UserInformationUpdateManager;
use App\Service\Profile\Settings\ProfilePictureService;
use App\Service\User\UserCheckerService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SettingsController extends AbstractController
{
    public function __construct(
        private readonly UserCheckerService $userCheckerService,
        private readonly ProfilePictureService $profilePictureService,
        private readonly UserInformationUpdateManager $userInformationUpdateManager,
    ) {}

    #[Route('/profile/settings', name: 'profile_settings')]
    public function showProfile(): Response
    {
        $user = $this->userCheckerService->getUserOrThrow();

        return $this->render('profile/settings/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profile/settings/edit', name: 'profile_settings_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        try {
            $userInformation = $this->userInformationUpdateManager->userInformationUpdate();
            $user = $userInformation->getUser();

            $hasAvatar = null !== $userInformation->getAvatarName();
            $form = $this->createForm(ProfileType::class, $user, [
                'has_avatar' => $hasAvatar,
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($form->get('userInformation')->has('remove_avatar') && $form->get('userInformation')->get('remove_avatar')->getData()) {
                    $result = $this->profilePictureService->removeAvatar($userInformation);
                    $this->addFlash($result['type'], $result['message']);
                }

                $entityManager->persist($userInformation);
                $entityManager->flush();

                $this->addFlash('success', 'Profile updated successfully.');

                return $this->redirectToRoute('profile_settings');
            }

            return $this->render('profile/settings/edit.html.twig', [
                'form' => $form->createView(),
            ]);
        } catch (Exception $exception) {
            $this->addFlash('danger', sprintf('An error occurred while updating the profile: %s', $exception->getMessage()));

            return $this->redirectToRoute('profile_settings_edit');
        }
    }
}
