<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\SkillRepository;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=SkillRepository::class)
 */
#[ApiResource(
  attributes: ["pagination_items_per_page" => 50],
  normalizationContext: ['groups' => 'read:skill'],
  denormalizationContext: ['groups' => 'write:skill'],
  collectionOperations: [
    'post' => ['security' => 'is_granted("ROLE_ADMIN")']
  ],
  itemOperations: [
    'get',
    'put' => ['security' => 'is_granted("ROLE_ADMIN")'],
    'delete' => ['security' => 'is_granted("ROLE_ADMIN")']
  ]
)]
class Skill
{
  /**
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   */
  #[Groups(['read:skill'])]
  private $id;

  /**
   * @ORM\Column(type="string", length=255)
   * @Assert\NotBlank
   */
  #[Groups(['read:skill', 'write:skill'])]
  private $skillKey;

  /**
   * @ORM\Column(type="integer")
   * @Assert\Range(min = 1, max = 2)
   */
  #[Groups(['read:skill', 'write:skill'])]
  private $side;

  /**
   * @ORM\ManyToOne(targetEntity=Weapon::class, inversedBy="skills")
   */
  #[Groups(['read:skill', 'write:skill'])]
  private $weapon;

  /**
   * @ORM\Column(type="integer")
   * @Assert\Range(min = 1, max = 5)
   */
  #[Groups(['read:skill', 'write:skill'])]
  private $col;

  /**
   * @ORM\Column(type="integer")
   * @Assert\Range(min = 1, max = 6)
   */
  #[Groups(['read:skill', 'write:skill'])]
  private $line;

  /**
   * @ORM\ManyToOne(targetEntity=Skill::class, inversedBy="children")
   * @ORM\JoinColumn(onDelete="SET NULL")
   * @var self
   */
  #[ApiProperty(readableLink: false, writableLink: false)]
  #[Groups(['read:skill', 'write:skill'])]
  private $parent;

  /**
   * @ORM\OneToMany(targetEntity=Skill::class, mappedBy="parent")
   * @var self
   */
  #[ApiProperty(readableLink: false, writableLink: false)]
  #[Groups(['read:skill'])]
  private $children;

  /**
   * @ORM\Column(type="integer", length=255)
   * @Assert\Range(min = 0, max = 6)
   */
  #[Groups(['read:skill', 'write:skill'])]
  private $bgColor;

  /**
   * @ORM\Column(type="integer")
   * @Assert\Range(min = 1, max = 4)
   */
  #[Groups(['read:skill', 'write:skill'])]
  private $type;

  /**
   * @ORM\Column(type="float", nullable=true)
   */
  #[Groups(['read:skill', 'write:skill'])]
  private $cooldown;

  public function __construct()
  {
    $this->children = new ArrayCollection();
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getSkillKey(): ?string
  {
    return $this->skillKey;
  }

  public function setSkillKey(string $skillKey): self
  {
    $this->skillKey = $skillKey;

    return $this;
  }

  public function getSide(): ?int
  {
    return $this->side;
  }

  public function setSide(int $side): self
  {
    $this->side = $side;

    return $this;
  }

  public function getWeapon(): ?Weapon
  {
    return $this->weapon;
  }

  public function setWeapon(?Weapon $weapon): self
  {
    $this->weapon = $weapon;

    return $this;
  }

  public function getCol(): ?int
  {
    return $this->col;
  }

  public function setCol(int $col): self
  {
    $this->col = $col;

    return $this;
  }

  public function getLine(): ?int
  {
    return $this->line;
  }

  public function setLine(int $line): self
  {
    $this->line = $line;

    return $this;
  }

  public function getParent(): ?self
  {
    return $this->parent;
  }

  public function setParent(?self $parent): self
  {
    $this->parent = $parent;

    return $this;
  }

  /**
   * @return Collection|self[]
   */
  public function getChildren(): Collection
  {
    return $this->children;
  }

  public function addChild(self $child): self
  {
    if (!$this->children->contains($child)) {
      $this->children[] = $child;
      $child->setParent($this);
    }

    return $this;
  }

  public function removeChild(self $child): self
  {
    if ($this->children->removeElement($child)) {
      // set the owning side to null (unless already changed)
      if ($child->getParent() === $this) {
        $child->setParent(null);
      }
    }

    return $this;
  }

  public function getBgColor(): ?string
  {
    return $this->bgColor;
  }

  public function setBgColor(string $bgColor): self
  {
    $this->bgColor = $bgColor;

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

  public function getCooldown(): ?float
  {
      return $this->cooldown;
  }

  public function setCooldown(?float $cooldown): self
  {
      $this->cooldown = $cooldown;

      return $this;
  }
}
