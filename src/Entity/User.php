<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity("email", message="email.already")
 * @UniqueEntity("pseudo", message="pseudo.already")
 */
class User implements UserInterface
{
  /**
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   */
  #[Groups(['read:build'])]
  private $id;

  /**
   * @ORM\Column(type="string", length=180, unique=true)
   */
  private $email;

  /**
   * @ORM\Column(type="json")
   */
  private $roles = [];

  /**
   * @var string The hashed password
   * @ORM\Column(type="string")
   */
  private $password;

  /**
   * @ORM\Column(type="string", length=255, unique=true)
   */
  #[Groups(['read:build'])]
  private $pseudo;

  /**
   * @ORM\OneToMany(targetEntity=Build::class, mappedBy="author", orphanRemoval=true)
   */
  private $builds;

  /**
   * @ORM\Column(type="boolean")
   */
  private $isVerified = false;

  /**
   * @ORM\ManyToMany(targetEntity=Build::class, mappedBy="favorites")
   */
  private $favorites;


  public function __construct()
  {
    $this->builds = new ArrayCollection();
    $this->favorites = new ArrayCollection();
  }

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

  /**
   * A visual identifier that represents this user.
   *
   * @see UserInterface
   */
  public function getUsername(): string
  {
    return (string) $this->email;
  }

  /**
   * @see UserInterface
   */
  public function getRoles(): array
  {
    $roles = $this->roles;
    // guarantee every user at least has ROLE_USER
    $roles[] = "ROLE_USER";

    return array_unique($roles);
  }

  public function setRoles(array $roles): self
  {
    $this->roles = $roles;

    return $this;
  }

  /**
   * @see UserInterface
   */
  public function getPassword(): string
  {
    return (string) $this->password;
  }

  public function setPassword(string $password): self
  {
    $this->password = $password;

    return $this;
  }

  /**
   * Returning a salt is only needed, if you are not using a modern
   * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
   *
   * @see UserInterface
   */
  public function getSalt(): ?string
  {
    return null;
  }

  /**
   * @see UserInterface
   */
  public function eraseCredentials()
  {
    // If you store any temporary, sensitive data on the user, clear it here
    // $this->plainPassword = null;
  }

  public function getPseudo(): ?string
  {
    return $this->pseudo;
  }

  public function setPseudo(string $pseudo): self
  {
    $this->pseudo = $pseudo;

    return $this;
  }

  public function isVerified(): bool
  {
    return $this->isVerified;
  }

  public function setIsVerified(bool $isVerified): self
  {
    $this->isVerified = $isVerified;

    return $this;
  }

  /**
   * @return Collection|Build[]
   */
  public function getBuilds(): Collection
  {
    return $this->builds;
  }

  public function addBuild(Build $build): self
  {
    if (!$this->builds->contains($build)) {
      $this->builds[] = $build;
      $build->setAuthor($this);
    }

    return $this;
  }

  public function removeBuild(Build $build): self
  {
    if ($this->builds->removeElement($build)) {
      // set the owning side to null (unless already changed)
      if ($build->getAuthor() === $this) {
        $build->setAuthor(null);
      }
    }

    return $this;
  }

  /**
   * @return Collection|Build[]
   */
  public function getFavorites(): Collection
  {
      return $this->favorites;
  }

  public function addFavorites(Build $favorites): self
  {
      if (!$this->favorites->contains($favorites)) {
          $this->favorites[] = $favorites;
          $favorites->addFavorites($this);
      }

      return $this;
  }

  public function removeFavorites(Build $favorites): self
  {
      if ($this->favorites->removeElement($favorites)) {
          $favorites->removeFavorites($this);
      }

      return $this;
  }
}
