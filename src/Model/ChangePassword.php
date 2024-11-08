<?php
namespace App\Model;

use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;

class ChangePassword
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     * @SecurityAssert\UserPassword(
     *     message = "Current password is incorrect, please try again"
     * )
     */
    private string $currentPassword;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     */
    private string $plainPassword;

    /**
     * @return string
     */
    public function getCurrentPassword(): string
    {
        return $this->currentPassword;
    }

    /**
     * @return string
     */
    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    /**
     * @param $password
     * @return $this
     */
    public function setCurrentPassword($password): self
    {
        $this->currentPassword = $password;

        return $this;
    }

    /**
     * @param $password
     * @return $this
     */
    public function setPlainPassword($password): self
    {
        $this->plainPassword = $password;

        return $this;
    }
}
