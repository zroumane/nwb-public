<?php

namespace App\Entity;

use App\Entity\User;
use App\Entity\Weapon;
use App\Validator\ActiveSkill;
use App\Validator\SelectedSkill;
use Doctrine\ORM\Mapping as ORM;
use App\Validator\JsonArrayLenght;
use App\Validator\Characteristics;
use App\Repository\BuildRepository;
use App\Serializer\UserOwnedInterface;
use App\Validator\Weapon as WeaponValidate;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=BuildRepository::class)
 * @ORM\Table(name="build", indexes={@ORM\Index(columns={"name"}, flags={"fulltext"})})
 * @ORM\HasLifecycleCallbacks()
 */
#[ApiResource(
  normalizationContext: ['groups' => 'read:build'],
  denormalizationContext: ['groups' => 'write:build'],
  collectionOperations: [
    'post'
  ],
  itemOperations: [
    'get',
    'put' => [
      'access_control' => 'is_granted("CheckUserBuild", object) or is_granted("ROLE_BUILD_ADMIN")'
    ]
  ]
)]
class Build implements UserOwnedInterface
{
  /**
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   */
  #[Groups(['read:build'])]
  private $id;

  /**
   * @ORM\Column(type="string", length=255)
   * @Assert\NotBlank
   * @Assert\Length(min = 8, max = 60)
   */
  #[Groups(['write:build'])]
  private $name;

  /**
   * @ORM\Column(type="text", nullable=true)
   * @Assert\Length(min = 0, max = 3000)
   */
  #[Groups(['write:build'])]
  private $description;

  /**
   * @ORM\Column(type="integer")
   * @Assert\Range(min = 1, max = 5)
   */
  #[Groups(['write:build'])]
  private $type;

  /**
   * @ORM\Column(type="datetime")
   */
  private $created_at;

  /**
   * @ORM\Column(type="datetime")
   */
  private $updated_at;
  
  /**
   * @ORM\Column(type="bigint")
   */
  private $views = 0;
  
  private $notSendDiscord = false;
  
  /**
   * @ORM\ManyToMany(targetEntity=User::class, inversedBy="favorites")
   */
  private $favorites;

  /**
   * @ORM\ManyToOne(targetEntity=User::class, inversedBy="builds")
   * @ORM\JoinColumn(nullable=true)
   */
  private $author;

  /**
   * @ORM\Column(type="array")
   * @JsonArrayLenght(2)
   * @WeaponValidate
   */
  #[Groups(['read:build', 'write:build'])]
  private $weapons = [];

  /**
   * @ORM\Column(type="json")
   * @JsonArrayLenght(2)
   * @SelectedSkill
   */
  #[Groups(['read:build', 'write:build'])]
  private $selectedSkills = [];

  /**
   * @ORM\Column(type="array")
   * @JsonArrayLenght(2)
   * @ActiveSkill
   */
  #[Groups(['read:build', 'write:build'])]
  private $activedSkills = [];

  /**
   * @ORM\Column(type="json")
   * @JsonArrayLenght(3)
   * @Characteristics
   */
  #[Groups(['read:build', 'write:build'])]
  private $characteristics = [];

  /**
   * @ORM\Column(type="boolean")
   */
  #[Groups(['read:build', 'write:build'])]
  private $private;


  /**
   * @ORM\PrePersist
   */
  public function setCreatedAtValue(): void
  {
    $this->setCreatedAt(new \DateTime("now"));
    $this->setUpdatedAt(new \DateTime("now"));
  }

  /**
   * @ORM\PreUpdate
   */
  public function setUpdateAtvalue(): void
  {
    if(!$this->getNotSendDiscord()){
      $this->setUpdatedAt(new \DateTime("now"));
    }
  }

  /**
   * @ORM\PostPersist
   */
  public function preUpdate(): void
  {
    $this->sendDiscordWebhook(false);
  }

  /**
   * @ORM\PostUpdate
   */
  public function postUpdate(): void
  {
    $this->sendDiscordWebhook(true);
  }
  
  public function sendDiscordWebhook($updated): void
  {
    if(!$this->getNotSendDiscord() && !$this->getPrivate()){

      $build = $this;
      $webhookurl = "";
      if($_SERVER['APP_ENV'] == "dev"){
        $webhookurl = "https://discord.com/api/webhooks/922956558202183802/Tw-LSFAa6kjsAWSZ1ScwB3hAnHQDrFgYNrKbRAMAUyo07XlNprrtvMeoKG_dfO8FjSto";
      }else{
        $webhookurl = "https://discord.com/api/webhooks/868344360524214272/_4onLMp3h0lU_8NZM5fhJZg4z5fedQ_RrHUH25L6OQpFrtijx5pgosgkU_WG_8HVGEoV";
      }

      
      $timestamp = date("c", strtotime("now"));
      $buildId = $build->getId();

      $embed = [
        "embeds" => [
          [
            "title" => $build->getName(),
            "url" => sprintf("https://newworld-builder.com/build/%d", $buildId),
            "timestamp" => $timestamp,
            "color" => hexdec("ffffff")
          ]
        ]
      ];
      
      $author = $build->getAuthor();
      $authorTitle = "";
      if($updated){
        $authorTitle = "Build edited by %s :";
      }else{
        $authorTitle = "New build by %s :";
      }
      $embed['embeds'][0]['author']['name'] = sprintf($authorTitle, $author ? $author->getPseudo() : '//');
      $embed['embeds'][0]['author']['url'] = $author ? sprintf("https://newworld-builder.com/profile/%d",$author->getId()) : null;

      $json_data = json_encode($embed, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );


      $ch = curl_init( $webhookurl );
      curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
      curl_setopt( $ch, CURLOPT_POST, 1);
      curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_data);
      curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
      curl_setopt( $ch, CURLOPT_HEADER, 0);
      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
      curl_exec( $ch );
      curl_close( $ch );
    }
  }

  
  public function __construct()
  {
    $this->weapons = new ArrayCollection();
    $this->favorites = new ArrayCollection();
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getName(): ?string
  {
    return $this->name;
  }

  public function setName(string $name): self
  {
    $this->name = $name;

    return $this;
  }

  public function getDescription(): ?string
  {
    return $this->description;
  }

  public function setDescription(?string $description): self
  {
    $this->description = $description;

    return $this;
  }

  public function getType(): ?int
  {
    return $this->type;
  }

  public function setType(int $type): self
  {
    $this->type = $type;

    return $this;
  }

  public function getCreatedAt(): ?\DateTimeInterface
  {
    return $this->created_at;
  }

  public function setCreatedAt(\DateTimeInterface $created_at): self
  {
    $this->created_at = $created_at;

    return $this;
  }

  public function getUpdatedAt(): ?\DateTimeInterface
  {
    return $this->updated_at;
  }

  public function setUpdatedAt(\DateTimeInterface $updated_at): self
  {
    $this->updated_at = $updated_at;

    return $this;
  }

  public function getViews(): ?string
  {
    return $this->views;
  }

  public function setViews(string $views): self
  {
    $this->views = $views;

    return $this;
  }

  public function getNotSendDiscord(): ?bool
  {
    return $this->notSendDiscord;
  }

  public function setNotSendDiscord(bool $notSendDiscord): self
  {
    $this->notSendDiscord = $notSendDiscord;

    return $this;
  }

  public function getAuthor(): ?User
  {
    return $this->author;
  }

  public function setAuthor(?User $author): self
  {
    $this->author = $author;

    return $this;
  }

  public function getSelectedSkills(): ?array
  {
    return $this->selectedSkills;
  }

  public function setSelectedSkills(array $selectedSkills): self
  {
    $this->selectedSkills = $selectedSkills;

    return $this;
  }

  public function getActivedSkills(): ?array
  {
    return $this->activedSkills;
  }

  public function setActivedSkills(array $activedSkills): self
  {
    $this->activedSkills = $activedSkills;

    return $this;
  }

  public function getWeapons()
  {
    return $this->weapons;
  }

  public function setWeapons(array $weapons): self
  {
    $this->weapons = $weapons;

    return $this;
  }

  public function removeWeapon(Weapon $weapon): self
  {
    $this->weapons->removeElement($weapon);

    return $this;
  }

  /**
   * @return Collection|User[]
   */
  public function getFavorites(): Collection
  {
      return $this->favorites;
  }

  public function addFavorites(User $favorites): self
  {
      if (!$this->favorites->contains($favorites)) {
          $this->favorites[] = $favorites;
      }

      return $this;
  }

  public function removeFavorites(User $favorites): self
  {
      $this->favorites->removeElement($favorites);

      return $this;
  }

  public function getCharacteristics(): ?array
  {
      return $this->characteristics;
  }

  public function setCharacteristics(array $characteristics): self
  {
      $this->characteristics = $characteristics;

      return $this;
  }

  public function getPrivate(): ?bool
  {
      return $this->private;
  }

  public function setPrivate(bool $private): self
  {
      $this->private = $private;

      return $this;
  }
}
