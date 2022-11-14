<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\ORM;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JuniWalk\Utils\Enums\Role;
use JuniWalk\Utils\Strings;
use Nette\Security\IIdentity as Identity;
use Stringable;

#[ORM\MappedSuperclass]
abstract class User implements Identity, Stringable
{
	use Traits\Identifier;
	use Traits\Activable;
	// use Traits\Parametrized;

	#[ORM\Column(type: 'string', length: 64, nullable: true)]
	protected ?string $name = null;

	#[ORM\Column(type: 'string', unique: true)]
	protected string $email;

	#[ORM\Column(type: 'string', length: 64, nullable: true)]
	protected ?string $password = null;

	#[ORM\Column(type: 'string', length: 32, enumType: Role::class)]
	protected Role $role = Role::User;

	#[ORM\Column(type: 'datetimetz', options: ['default' => 'now()'])]
	protected DateTime $signUp;

	#[ORM\Column(type: 'datetimetz', nullable: true)]
	protected ?DateTime $signIn = null;


	public function __construct(string $email, string $name = null)
	{
		$this->signUp = new DateTime;
		$this->setEmail($email);
		$this->setName($name);
	}


	public function __toString(): string
	{
		return Strings::webalize($this->id.'-'.$this->email);
	}


	public function setName(?string $name): void
	{
		$this->name = $name ?: null;
	}


	public function getName(): ?string
	{
		return $this->name;
	}


	public function getDisplayName(): string
	{
		return $this->name ?: $this->email;
	}


	public function setEmail(string $email): void
	{
		$this->email = Strings::lower($email);
	}


	public function getEmail(): string
	{
		return $this->email;
	}


	public function setPassword(?string $password): void
	{
		if (!empty($password)) {
			$password = password_hash($password, PASSWORD_DEFAULT);
		}

		$this->password = $password ?: null;
	}


	public function isPasswordValid(string $password): bool
	{
		if (is_null($this->password)) {
			return false;
		}

		return password_verify($password, $this->password);
	}


	public function isPasswordUpToDate(): bool
	{
		return !password_needs_rehash($this->password, PASSWORD_DEFAULT);
	}


	public function setRole(Role $role): void
	{
		$this->role = $role;
	}


	public function getRole(): Role
	{
		return $this->role;
	}


	public function getRoles(): array
	{
		return [$this->role];
	}


	public function getSignUp(): DateTime
	{
		return clone $this->signUp;
	}


	public function setSignIn(?DateTime $signIn): void
	{
		$signIn = $signIn ?: new DateTime;
		$this->signIn = clone $signIn;
	}


	public function getSignin(): ?DateTime
	{
		if (!$this->signIn) {
			return null;
		}

		return clone $this->signIn;
	}
}
