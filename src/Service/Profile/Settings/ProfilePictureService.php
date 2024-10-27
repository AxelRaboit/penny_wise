<?php

declare(strict_types=1);

namespace App\Service\Profile\Settings;

use App\Entity\UserInformation;
use App\Util\StringHelper;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

final readonly class ProfilePictureService
{
    public function __construct(
        #[Autowire('%avatars_directory%')]
        private readonly string $avatarsDirectory
    ) {}

    /**
     * Remove the avatar of the user and return the result status.
     *
     * @return array{type: string, message: string}
     */
    public function removeAvatar(UserInformation $userInformation): array
    {
        $filesystem = new Filesystem();
        $oldAvatarName = $userInformation->getAvatarName();

        if (StringHelper::isNotEmpty($oldAvatarName)) {
            $avatarPath = sprintf('%s/%s', $this->avatarsDirectory, $oldAvatarName);

            if ($filesystem->exists($avatarPath)) {
                try {
                    $filesystem->remove($avatarPath);
                    $userInformation->setAvatarName(null);

                    return ['type' => 'success', 'message' => 'Avatar successfully removed.'];
                } catch (IOExceptionInterface $exception) {
                    return ['type' => 'danger', 'message' => sprintf('An error occurred while deleting the avatar: %s', $exception->getMessage())];
                }
            } else {
                return ['type' => 'warning', 'message' => 'Avatar file does not exist.'];
            }
        }

        return ['type' => 'info', 'message' => 'No avatar to remove.'];
    }
}
