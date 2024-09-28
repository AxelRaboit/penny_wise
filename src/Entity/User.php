<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\TimestampableTrait;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'user_id_seq', allocationSize: 1, initialValue: 1)]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private string $password;

    /**
     * @var Collection<int, Wallet>
     */
    #[ORM\OneToMany(targetEntity: Wallet::class, mappedBy: 'individual')]
    private Collection $wallets;

    #[ORM\OneToOne(targetEntity: UserInformation::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?UserInformation $userInformation = null;

    #[ORM\Column]
    private ?bool $active = null;

    /**
     * @var Collection<int, TransactionTag>
     */
    #[ORM\OneToMany(targetEntity: TransactionTag::class, mappedBy: 'user')]
    private Collection $transactionTags;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function __construct()
    {
        $this->wallets = new ArrayCollection();
        $this->transactionTags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    #[Override]
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    #[Override]
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    #[Override]
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    #[Override]
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Wallet>
     */
    public function getWallets(): Collection
    {
        return $this->wallets;
    }

    public function addWallet(Wallet $wallet): static
    {
        if (!$this->wallets->contains($wallet)) {
            $this->wallets->add($wallet);
            $wallet->setIndividual($this);
        }

        return $this;
    }

    public function getUserInformation(): ?UserInformation
    {
        return $this->userInformation;
    }

    public function setUserInformation(?UserInformation $userInformation): self
    {
        $this->userInformation = $userInformation;

        return $this;
    }

    /**
     * Serializes the user object to an array.
     *
     * @return array<string, int|string|null> The array representation of the user object
     */
    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'password' => $this->password,
        ];
    }

    /**
     * Unserializes the user data from an array.
     *
     * @param array<string, mixed> $data The serialized user data
     */
    public function __unserialize(array $data): void
    {
        $this->id = isset($data['id']) && is_int($data['id']) ? $data['id'] : null;
        $this->email = isset($data['email']) && is_string($data['email']) ? $data['email'] : null;
        $this->password = isset($data['password']) && is_string($data['password']) ? $data['password'] : '';
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection<int, TransactionTag>
     */
    public function getTransactionTags(): Collection
    {
        return $this->transactionTags;
    }

    public function addTransactionTag(TransactionTag $transactionTag): self
    {
        if (!$this->transactionTags->contains($transactionTag)) {
            $this->transactionTags[] = $transactionTag;
            $transactionTag->setUser($this);
        }

        return $this;
    }

    public function removeTransactionTag(TransactionTag $transactionTag): self
    {
        if ($this->transactionTags->removeElement($transactionTag) && $transactionTag->getUser() === $this) {
            $transactionTag->setUser(null);
        }

        return $this;
    }
}
