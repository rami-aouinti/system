<?php

declare(strict_types=1);

namespace App\User\Domain\Entity\Interfaces;

/**
 * @package App\User
 */
interface UserInterface
{
    public function getId(): string;
    public function getUsername(): string;
    public function getEmail(): string;
}
