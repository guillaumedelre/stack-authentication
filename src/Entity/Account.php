<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\AccountRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Generator\Uuidv1Generator;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={"order"={"email": "ASC"}},
 *     normalizationContext={"groups"={"api:account:get"}},
 *     itemOperations={
 *         "get""={
 *             "method"="get"
 *         },
 *         "put"={
 *             "method"="put",
 *             "denormalization_context"={"groups"={"api:account:put"}}
 *         },
 *         "patch"={
 *             "denormalization_context"={"groups"={"api:account:patch"}}
 *         }
 *     },
 *     collectionOperations={
 *         "get""={
 *             "method"="get"
 *         },
 *         "post"={
 *             "denormalization_context"={"groups"={"api:account:post"}}
 *         }
 *     }
 * )
 * @ORM\Entity(repositoryClass=AccountRepository::class)
 * @ORM\Table(name="account")
 */
class Account implements UserInterface
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=Uuidv1Generator::class)
     * @Assert\Uuid(versions={1})
     */
    private UuidInterface $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private string $email;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank(allowNull=true)
     */
    private ?string $fullname = null;

    /**
     * @ORM\Column(type="json")
     */
    private array $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private string $password;

    /**
     * @var ?string The readable password
     */
    private ?string $plainPassword = null;

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    /**
     * @param UuidInterface|string $id
     *
     * @return $this
     */
    public function setId($id): self
    {
        $this->id = is_string($id) ? Uuid::fromString($id) : $id;

        return $this;
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

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(?string $fullname): self
    {
        $this->fullname = $fullname;

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
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $password = null): self
    {
        $this->plainPassword = $password;

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
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
