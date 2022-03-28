<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Validator\JsonArrayLenght;
use App\Repository\WeaponRepository;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=WeaponRepository::class)
 */
#[ApiResource(
	attributes: ["pagination_items_per_page" => 50],
	normalizationContext: ['groups' => 'read:weapon'],
	denormalizationContext: ['groups' => 'write:weapon'],
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
class Weapon
{
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	#[Groups(['read:weapon'])]
	private $id;

	/**
	 * @ORM\Column(type="string", length=255)
	 * @Assert\NotBlank
	 */
	#[Groups(['read:weapon', 'write:weapon'])]
	private $weaponKey;

	/**
	 * @ORM\OneToMany(targetEntity=Skill::class, mappedBy="weapon", cascade={"remove"})
	 */
	#[ApiSubresource()]
	private $skills;

	/**
	 * @ORM\Column(type="json")
	 * @JsonArrayLenght(2)
	 * @Assert\NotBlank
	 */
	#[Groups(['read:weapon', 'write:weapon'])]
	private $branch = [];

	public function __construct()
	{
		$this->skills = new ArrayCollection();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getWeaponKey(): ?string
	{
		return $this->weaponKey;
	}

	public function setWeaponKey(string $weaponKey): self
	{
		$this->weaponKey = $weaponKey;

		return $this;
	}

	/**
	 * @return Collection|Skill[]
	 */
	public function getSkills(): Collection
	{
		return $this->skills;
	}

	public function addSkill(Skill $skill): self
	{
		if (!$this->skills->contains($skill)) {
			$this->skills[] = $skill;
			$skill->setWeapon($this);
		}

		return $this;
	}

	public function removeSkill(Skill $skill): self
	{
		if ($this->skills->removeElement($skill)) {
			// set the owning side to null (unless already changed)
			if ($skill->getWeapon() === $this) {
				$skill->setWeapon(null);
			}
		}

		return $this;
	}

	public function getBranch(): ?array
	{
		return $this->branch;
	}

	public function setBranch(array $branch): self
	{
		$this->branch = $branch;

		return $this;
	}
}
