<?php

namespace App\Core\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Core\Enum\AddressType;
use App\Core\Repository\AddressRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Gedmo\Blameable\Blameable;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Timestampable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['address:view']]),
        new GetCollection(normalizationContext: ['groups' => ['address:view']]),
        new Post(),
        new Put(),
        new Patch()
    ],
)]
#[Entity(repositoryClass: AddressRepository::class)]
class Address implements Blameable, Timestampable
{
    use BlameableEntity;
    use TimestampableEntity;

    #[Id]
    #[Column(type: UuidType::NAME, unique: true)]
    #[Groups(['user:view', 'company:view', 'user:write', 'address:view', 'workflow:view', 'shipment:view', 'document:view'])]
    private ?Uuid $id;

    #[Column(enumType: AddressType::class)]
    #[Groups(['user:view', 'company:view', 'user:write', 'shipment:write', 'company:write', 'address:view', 'workflow:view', 'shipment:view', 'document:view', 'user_public:view', 'workflow:write'])]
    private AddressType $addressType = AddressType::DEFAULT;

    #[Column]
    #[Groups(['user:view', 'company:view', 'user:write', 'shipment:write', 'company:write', 'address:view', 'workflow:view', 'shipment:view', 'document:view', 'user_public:view', 'workflow:write'])]
    #[Assert\Regex(pattern: "/^([+]?[\s0-9]+)?(\d{3}|[(]?[0-9]+[)])?([-]?[\s]?[0-9])+$/")]
    private string $phoneNumber;

    #[Column]
    #[Groups(['user:view', 'company:view', 'user:write', 'shipment:write', 'company:write', 'address:view', 'workflow:view', 'shipment:view', 'document:view', 'user_public:view', 'workflow:write'])]
    #[Assert\Regex(pattern: "/^\s*\S+(?:\s+\S+)+/")]
    #[Assert\Length(min: 1, max: 50)]
    private string $street;

    #[Column]
    #[Groups(['user:view', 'company:view', 'user:write', 'shipment:write', 'company:write', 'address:view', 'workflow:view', 'shipment:view', 'document:view', 'user_public:view', 'workflow:write'])]
    #[Assert\Length(min: 1, max: 50)]
    private string $apartment;

    #[Column]
    #[Groups(['user:view', 'company:view', 'user:write', 'shipment:write', 'company:write', 'address:view', 'workflow:view', 'shipment:view', 'document:view', 'user_public:view', 'workflow:write'])]
    #[Assert\Regex(pattern: "/^\d{2}-\d{3}/")]
    private string $postalCode;

    #[Column]
    #[Groups(['user:view', 'company:view', 'user:write', 'shipment:write', 'company:write', 'address:view', 'workflow:view', 'shipment:view', 'document:view', 'user_public:view', 'workflow:write'])]
    #[Assert\Regex('/^[A-Za-zżźćńółęąśŻŹĆĄŚĘŁÓŃ]+(?:[\s-][A-Za-zżźćńółęąśŻŹĆĄŚĘŁÓŃ]+)*$/')]
    private string $town;

    #[Column]
    #[Groups(['user:view', 'company:view', 'user:write', 'shipment:write', 'company:write', 'address:view', 'workflow:view', 'shipment:view', 'document:view', 'user_public:view', 'workflow:write'])]
    #[Assert\Country()]
    private string $country;

    /**
     * @var ArrayCollection<int, Company>
     */
    #[ORM\OneToMany(mappedBy: 'address', targetEntity: Company::class)]
    private Collection $companies;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->companies = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getAddressType(): AddressType
    {
        return $this->addressType;
    }

    public function setAddressType(AddressType $addressType): static
    {
        $this->addressType = $addressType;

        return $this;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(string $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getApartment(): string
    {
        return $this->apartment;
    }

    public function setApartment(string $apartment): static
    {
        $this->apartment = $apartment;

        return $this;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getTown(): string
    {
        return $this->town;
    }

    public function setTown(string $town): static
    {
        $this->town = $town;

        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return ArrayCollection<int, Company>
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    /**
     * @param ArrayCollection<int, Company> $companies
     */
    public function setCompanies(Collection $companies): void
    {
        $this->companies = $companies;
    }

    #[Groups(['address:view'])]
    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    #[Groups(['address:view'])]
    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    #[Groups(['address:view'])]
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    #[Groups(['address:view'])]
    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }
}
