<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserInformationRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Ignore;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: UserInformationRepository::class)]
#[Vich\Uploadable]
class UserInformation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'user_information_id_seq', allocationSize: 1, initialValue: 1)]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'userInformation')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastname = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $avatarName = null;

    /**
     * @ignore
     */
    #[Vich\UploadableField(mapping: 'user_avatars', fileNameProperty: 'avatarName')]
    #[Ignore]
    private ?File $avatarFile = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bio = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getAvatarName(): ?string
    {
        return $this->avatarName;
    }

    public function setAvatarName(?string $avatarName): self
    {
        $this->avatarName = $avatarName;

        return $this;
    }

    public function setAvatarFile(?File $avatarFile = null): self
    {
        $this->avatarFile = $avatarFile;

        if ($avatarFile instanceof File) {
            $this->updatedAt = new DateTimeImmutable();
        }

        return $this;
    }

    public function getAvatarFile(): ?File
    {
        return $this->avatarFile;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;

        return $this;
    }
}
