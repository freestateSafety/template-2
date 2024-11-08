<?php

namespace App\Entity;

use Defuse\Crypto\KeyProtectedByPassword;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Customer
 *
 * @ORM\Table(name="customer")
 * @ORM\Entity(repositoryClass="App\Repository\CustomerRepository")
 * @UniqueEntity("email", message="E-Mail address is already in use. Please use the login form on the right to continue. If you have forgotten your password, click the forgot password link.")
 */
class Customer implements UserInterface, PasswordAuthenticatedUserInterface, \Serializable
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @var string
     *
     * @ORM\Column(name="email", length=200, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private string $email;

    /**
     * @var ?string
     *
     * @ORM\Column(name="password", length=64)
     */
    private ?string $password;

    /**
     * @var ?string
     *
     * @Assert\Length(max=4096)
     */
    private ?string $plainPassword;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", length=50)
     * @Assert\NotBlank()
     */
    private string $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", length=50)
     * @Assert\NotBlank()
     */
    private string $lastName;

    /**
     * @var ?string
     *
     * @ORM\Column(name="company", length=200, nullable=true)
     */
    private ?string $company;

    /**
     * @var ?string
     *
     * @ORM\Column(name="phone", length=15, nullable=true)
     */
    private ?string $phone;

    /**
     * @var ?string
     *
     * @ORM\Column(name="encryptionKey", type="text")
     */
    private ?string $key;

    /**
     * @var ?\DateTimeInterface
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private ?\DateTimeInterface $created;

    /**
     * @var ?\DateTimeInterface
     *
     * @ORM\Column(name="last_login", type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $lastLogin;

    /**
     * @ORM\Column(name="login_count", type="integer", options={"default": 0})
     */
    private int $loginCount = 0;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Address", mappedBy="customer", cascade={"all"}, orphanRemoval=true)
     */
    private ?Collection $addresses;

    /**
     * @ORM\Column(name="role", type="string", columnDefinition="ENUM('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN') NOT NULL DEFAULT 'ROLE_USER'")
     */
    private string $role = 'ROLE_USER';

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Order", mappedBy="customer", cascade={"all"}, orphanRemoval=true)
     */
    private ?Collection $orders;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\PasswordToken", mappedBy="customer", cascade={"all"}, orphanRemoval=true)
     */
    private ?PasswordToken $passwordToken;

    public function __construct()
    {
        $this->addresses = new ArrayCollection();
        $this->created = new \DateTime();
        $this->orders = new ArrayCollection();
    }

    /**
     * @return array
     */
    public function __serialize()
    {
        return [$this->id, $this->email, $this->password];
    }

    public function __unserialize(array $data)
    {
        [$this->id, $this->email, $this->password] = $data;
    }

    /**
     * Add addresses
     *
     * @param Address $addresses
     * @return $this
     */
    public function addAddress(Address $addresses): self
    {
        $this->addresses->add($addresses);

        return $this;
    }

    /**
     * @param Order $order
     * @return $this
     */
    public function addOrder(Order $order): self
    {
        $this->orders->add($order);

        return $this;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     * 
     * @return $this
     */
    public function eraseCredentials(): self
    {
        unset($this->plainPassword);
        return $this;
    }

    /**
     * Get addresses
     *
     * @param ?string $type
     * @return Collection
     */
    public function getAddresses(string $type = null): Collection
    {
        if (!is_null($type)) {
            return $this->addresses->filter(fn($address) => $address->getType() === $type);
        }

        return $this->addresses;
    }

    /**
     * @return Collection
     */
    public function getAddressesBilling(): Collection
    {
        return $this->getAddresses(Address::TYPE_BILLING);
    }

    /**
     * @return Collection
     */
    public function getAddressesShipping(): Collection
    {
        return $this->getAddresses(Address::TYPE_SHIPPING);
    }

    /**
     * @return ?string
     */
    public function getCompany(): ?string
    {
        return $this->company;
    }

    /**
     * @return ?\DateTime
     */
    public function getCreated(): ?\DateTime
    {
        return $this->created;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function getKey(): KeyProtectedByPassword
    {
        return KeyProtectedByPassword::loadFromAsciiSafeString($this->key);
    }

    /**
     * @return ?\DateTimeInterface
     */
    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return int
     */
    public function getLoginCount(): int
    {
        return $this->loginCount;
    }

    public function getName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * @return ?Collection
     */
    public function getOrders(): ?Collection
    {
        return $this->orders;
    }

    /**
     * @return ?string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @return ?string
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @return ?string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return string[] The user roles
     */
    public function getRoles(): array
    {
        return [$this->getRole()];
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername(): string
    {
        return $this->getEmail();
    }

    /**
     * @return string
     */
    public function getUserIdentifier(): string
    {
        return $this->getEmail();
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN']);
    }

    /**
     * Remove addresses
     *
     * @return $this
     */
    public function removeAddress(Address $addresses): self
    {
        $this->addresses->removeElement($addresses);
        return $this;
    }

    /**
     * @param Order $order
     * @return $this
     */
    public function removeOrder(Order $order): self
    {
        $this->orders->removeElement($order);
        return $this;
    }

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize(): string
    {
        return serialize($this->__serialize());
    }

    /**
     * @param string $company
     * @return $this
     */
    public function setCompany(string $company): self
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return $this
     */
    public function setCreated(\DateTime $created): self
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @param string $firstName
     * @return $this
     */
    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @param KeyProtectedByPassword $key
     * @return $this
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public function setKey(KeyProtectedByPassword $key): self
    {
        $this->key = $key->saveToAsciiSafeString();

        return $this;
    }

    /**
     * Set lastLogin
     *
     * @param \DateTime $lastLogin
     * @return $this
     */
    public function setLastLogin(\DateTime $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    /**
     * @param string $lastName
     * @return $this
     */
    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Set loginCount
     *
     * @param integer $loginCount
     * @return $this
     */
    public function setLoginCount(int $loginCount): self
    {
        $this->loginCount = $loginCount;

        return $this;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPlainPassword(string $password): self
    {
        $this->plainPassword = $password;

        return $this;
    }

    /**
     * @param string $phone
     * @return $this
     */
    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @param string $role
     * @return $this
     */
    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $data <p>
     * The string representation of the object.
     * </p>
     * @return $this
     * @since 5.1.0
     */
    public function unserialize(string $data): self
    {
        $this->__unserialize(unserialize($data));
        return $this;
    }
}
