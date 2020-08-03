<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface, \Serializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * $username = <lms_domain>||<lms_user_id>
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $roles = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Institution", inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private $institution;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $lmsUserId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $apiKey;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $refreshToken;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lastLogin;

    private $encodedKey = 'niLb/WbAODNi7E4ccHHa/pPU3Bd9h6z1NXmjA981D4o=';

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
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

    /**
     * @see UserInterface
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed for apps that do not check user passwords
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

    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): self
    {
        $this->institution = $institution;

        return $this;
    }

    public function getLmsUserId(): ?string
    {
        return $this->lmsUserId;
    }

    public function setLmsUserId(string $lmsUserId): self
    {
        $this->lmsUserId = $lmsUserId;

        return $this;
    }

    public function getApiKey(): ?string
    {
        if (empty($this->apiKey)) {
            return false;
        }
        
        return $this->decryptData($this->apiKey);
    }

    public function setApiKey(?string $apiKey): self
    {
        $this->apiKey = $this->encryptData($apiKey);

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(?string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(\DateTimeInterface $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->lmsUserId
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->username,
            $this->lmsUserId
        ) = unserialize($serialized);
    }

    /**
     * @param $data
     *
     * @return string
     */
    private function encryptData($data)
    {
        $key   = base64_decode($this->encodedKey);
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $encrypted_data = sodium_crypto_secretbox($data, $nonce, $key);

        return base64_encode($nonce . $encrypted_data);
    }

    /**
     * @param $encrypted
     *
     * @return bool|string
     */
    private function decryptData($encrypted)
    {
        $key     = base64_decode($this->encodedKey);
        $decoded = base64_decode($encrypted);
        $nonce   = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
        $encrypted_text = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, NULL, '8bit');

        return sodium_crypto_secretbox_open($encrypted_text, $nonce, $key);
    }
}