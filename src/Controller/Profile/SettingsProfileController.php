<?php

declare(strict_types=1);

namespace App\Controller\Profile;

use App\Entity\User;
use App\Entity\UserInformation;
use App\Form\Profile\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SettingsProfileController extends AbstractController
{
    #[Route('/settings/profile', name: 'settings_profile')]
    public function showProfile(): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found.');
        }

        return $this->render('settings/profile/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/settings/profile/edit', name: 'settings_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $userInformation = $user->getUserInformation() ?? new UserInformation();
        $userInformation->setUser($user);

        $hasAvatar = null !== $userInformation->getAvatarName();

        $form = $this->createForm(ProfileType::class, $user, [
            'has_avatar' => $hasAvatar,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('userInformation')->has('remove_avatar') && $form->get('userInformation')->get('remove_avatar')->getData()) {
                $oldAvatarName = $userInformation->getAvatarName();
                if ($oldAvatarName) {
                    $filesystem = new Filesystem();

                    $avatarDirectory = $this->getParameter('avatars_directory');
                    if (is_string($avatarDirectory)) {
                        $avatarPath = sprintf('%s/%s', $avatarDirectory, $oldAvatarName);

                        if ($filesystem->exists($avatarPath)) {
                            try {
                                $filesystem->remove($avatarPath);
                                $this->addFlash('success', 'Avatar successfully removed.');
                            } catch (IOExceptionInterface $exception) {
                                $this->addFlash('danger', sprintf('An error occurred while deleting the avatar: %s', $exception->getMessage()));
                            }
                        } else {
                            $this->addFlash('warning', 'Avatar file does not exist.');
                        }
                    } else {
                        $this->addFlash('danger', 'Avatar directory is not properly configured.');
                    }

                    $userInformation->setAvatarName(null);
                }
            }

            $entityManager->persist($userInformation);
            $entityManager->flush();

            $this->addFlash('success', 'Profile updated successfully.');

            return $this->redirectToRoute('settings_profile');
        }

        return $this->render('settings/profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
