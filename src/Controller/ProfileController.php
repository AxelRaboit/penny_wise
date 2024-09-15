<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\UserInformation;
use App\Form\UserInformationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'profile_show')]
    public function showProfile(): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found.');
        }

        return $this->render('profile/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profile/edit', name: 'profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $userInformation = $user->getUserInformation() ?? new UserInformation();
        $userInformation->setUser($user);

        $form = $this->createForm(UserInformationType::class, $userInformation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('remove_avatar')->getData()) {
                $oldAvatarName = $userInformation->getAvatarName();
                if ($oldAvatarName) {
                    $avatarPath = $this->getParameter('avatars_directory') . '/' . $oldAvatarName;
                    if (file_exists($avatarPath)) {
                        unlink($avatarPath);
                    }

                    $userInformation->setAvatarName(null);
                }
            }

            $entityManager->persist($userInformation);
            $entityManager->flush();

            $this->addFlash('success', 'Profile updated successfully.');

            return $this->redirectToRoute('profile_show');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
