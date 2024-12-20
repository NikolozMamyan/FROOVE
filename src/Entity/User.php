<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Exception\InsufficientPointsException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas valide.")]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom complet est obligatoire.")]
    private ?string $fullName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $activationToken = null;

    #[ORM\Column]
    private ?bool $isVerified = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $devise = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\PositiveOrZero(message: 'Les points ne peuvent pas être négatifs.')]
    private int $points = 0;

    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'sender')]
    private Collection $messages;

    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'contacts', cascade: ['persist'])]
    #[ORM\JoinTable(name: 'user_contacts')]
    private Collection $contacts;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->contacts = new ArrayCollection();
    }

    // Getters & Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // No temporary sensitive data stored
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;
        return $this;
    }

    public function getActivationToken(): ?string
    {
        return $this->activationToken;
    }

    public function setActivationToken(?string $activationToken): self
    {
        $this->activationToken = $activationToken;
        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    public function getDevise(): ?string
    {
        return $this->devise;
    }

    public function setDevise(?string $devise): self
    {
        $this->devise = $devise;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;
        return $this;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $points): self
    {
        $this->points = $points;
        return $this;
    }

    public function addPoints(int $points): self
    {
        $this->points += $points;
        return $this;
    }

    public function removePoints(int $points): self
    {
        if ($this->points >= $points) {
            $this->points -= $points;
        } else {
            throw new InsufficientPointsException();
        }
        return $this;
    }

    public function participateInAd(Ad $ad): void
    {
        $price = $ad->getPrice();

        if ($this->points < $price) {
            throw new InsufficientPointsException("Vous n'avez pas assez de points pour participer.");
        }

        $this->removePoints($price);
    }

    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setSender($this);
        }
        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            if ($message->getSender() === $this) {
                $message->setSender(null);
            }
        }
        return $this;
    }

    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(User $contact): self
    {
        if (!$this->contacts->contains($contact) && $contact !== $this) {
            $this->contacts->add($contact);
            $contact->addContact($this);
        }
        return $this;
    }

    public function removeContact(User $contact): self
    {
        if ($this->contacts->contains($contact)) {
            $this->contacts->removeElement($contact);
            $contact->removeContact($this);
        }
        return $this;
    }
}
