<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=ItemRepository::class)
 */
#[ApiResource(
	attributes: ["pagination_enabled" => false, "order" => ["position" => "ASC"]],
    normalizationContext: ['groups' => 'read:item'],
    denormalizationContext: ['groups' => 'write:item'],
	collectionOperations: [
        'post' => ['security' => 'is_granted("ROLE_ADMIN")']
    ],
	itemOperations: [
        'get',
        'put' => ['security' => 'is_granted("ROLE_ADMIN")'],
        'delete' => ['security' => 'is_granted("ROLE_ADMIN")']
    ],
    subresourceOperations: [
        'api_item_categories_items_get_subresource' => [
            'method' => 'GET',
            'normalization_context' => [
                'groups' => ['read:itemCollection'],
            ],
        ],
        'api_item_tags_items_get_subresource' => [
            'method' => 'GET',
            'normalization_context' => [
                'groups' => ['read:itemCollection'],
            ],
        ],
    ],
)]
/**
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 */
class Item
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['read:itemCollection'])]
    private $id;

    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(name="position", type="integer")
     */
    #[Groups(['read:itemCollection', 'write:item'])]
    private $position;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Groups(['read:itemCollection', 'write:item'])]
    private $itemKey;

    /**
     * @ORM\Column(type="json")
     */
    #[Groups(['read:itemCollection', 'write:item'])]
    private $craft = [];

    /**
     * @ORM\ManyToOne(targetEntity=ItemCategory::class, inversedBy="items")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Gedmo\SortableGroup
     */
    #[Groups(['read:itemCollection' ,'write:item'])]
    private $category;

    /**
     * @ORM\ManyToMany(targetEntity=ItemTag::class, inversedBy="items")
     */
    #[Groups(['read:itemCollection', 'write:item'])]
    private $tag;

    public function __construct()
    {
        $this->tag = new ArrayCollection();
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

    public function getItemKey(): ?string
    {
        return $this->itemKey;
    }

    public function setItemKey(string $itemKey): self
    {
        $this->itemKey = $itemKey;

        return $this;
    }

    public function getCraft(): ?array
    {
        return $this->craft;
    }

    public function setCraft(array $craft): self
    {
        $this->craft = $craft;

        return $this;
    }

    public function getCategory(): ?ItemCategory
    {
        return $this->category;
    }

    public function setCategory(?ItemCategory $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection|ItemTag[]
     */
    public function getTag(): Collection
    {
        return $this->tag;
    }

    public function addTag(ItemTag $tag): self
    {
        if (!$this->tag->contains($tag)) {
            $this->tag[] = $tag;
        }

        return $this;
    }

    public function removeTag(ItemTag $tag): self
    {
        $this->tag->removeElement($tag);

        return $this;
    }
}
