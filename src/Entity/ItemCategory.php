<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;

#[ApiResource(
    attributes: ["pagination_enabled" => false, "order" => ["position" => "ASC"]],
    normalizationContext: ['groups' => 'read:itemCategory'],
    denormalizationContext: ['groups' => 'write:itemCategory'],
    collectionOperations: [
        'get',
        'post' => ['security' => 'is_granted("ROLE_ADMIN")']
    ],
    itemOperations: [
        'get',
        'put' => ['security' => 'is_granted("ROLE_ADMIN")'],
        'delete' => ['security' => 'is_granted("ROLE_ADMIN")']
    ]
)]
/**
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 */
class ItemCategory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['read:itemCategory'])]
    private $id;

    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(name="position", type="integer")
     */
    #[Groups(['read:itemCategory', 'write:itemCategory'])]
    private $position;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Groups(['read:itemCategory', 'write:itemCategory'])]
    private $category;

    /**
     * @Gedmo\SortableGroup
     * @ORM\ManyToOne(targetEntity=ItemCategory::class, inversedBy="children")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    #[Groups(['read:itemCategory', 'write:itemCategory'])]
    #[ApiProperty(readableLink: false, writableLink: false)]
    private $parent = null;
    
    /**
     * @ORM\OneToMany(targetEntity=ItemCategory::class, mappedBy="parent")
     * @OrderBy({"position" = "ASC"})
     */
    #[Groups(['read:itemCategory'])]
    private $children;

    /**
     * @ORM\OneToMany(targetEntity=Item::class, mappedBy="category")
     */
    #[ApiSubresource()]
    private $items;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setPosition($position)
    {
        $this->position = $position;
        
        return $this;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

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

    /**
     * @return Collection|Item[]
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(Item $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setCategory($this);
        }

        return $this;
    }

    public function removeItem(Item $item): self
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getCategory() === $this) {
                $item->setCategory(null);
            }
        }

        return $this;
    }
}
