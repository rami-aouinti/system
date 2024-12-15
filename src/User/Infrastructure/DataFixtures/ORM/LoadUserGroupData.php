<?php

declare(strict_types=1);

namespace App\User\Infrastructure\DataFixtures\ORM;

use App\General\Domain\Rest\UuidHelper;
use App\Role\Application\Security\Interfaces\RolesServiceInterface;
use App\Role\Domain\Entity\Role;
use App\Tests\Utils\PhpUnitUtil;
use App\User\Domain\Entity\UserGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;
use Throwable;

use function array_map;

/**
 * @package App\User
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class LoadUserGroupData extends Fixture implements OrderedFixtureInterface
{
    /**
     * @var array<string, string>
     */
    public static array $uuids = [
        'Role-logged' => '10000000-0000-1000-8000-000000000001',
        'Role-api' => '10000000-0000-1000-8000-000000000002',
        'Role-user' => '10000000-0000-1000-8000-000000000003',
        'Role-admin' => '10000000-0000-1000-8000-000000000004',
        'Role-root' => '10000000-0000-1000-8000-000000000005',
    ];

    public function __construct(
        private readonly RolesServiceInterface $rolesService,
    ) {
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @throws Throwable
     */
    #[Override]
    public function load(ObjectManager $manager): void
    {
        // Create entities
        array_map(fn (string $role): bool => $this->createUserGroup($manager, $role), $this->rolesService->getRoles());
        // Flush database changes
        $manager->flush();
    }

    /**
     * Get the order of this fixture
     */
    #[Override]
    public function getOrder(): int
    {
        return 2;
    }

    public static function getUuidByKey(string $key): string
    {
        return self::$uuids[$key];
    }

    /**
     * Method to create UserGroup entity for specified role.
     *
     * @throws Throwable
     */
    private function createUserGroup(ObjectManager $manager, string $role): bool
    {
        /** @var Role $roleReference */
        $roleReference = $this->getReference('Role-' . $this->rolesService->getShort($role), Role::class);

        // Create new entity
        $entity = new UserGroup();
        $entity->setRole($roleReference);
        $entity->setName($this->rolesService->getRoleLabel($role));

        PhpUnitUtil::setProperty(
            'id',
            UuidHelper::fromString(self::$uuids['Role-' . $this->rolesService->getShort($role)]),
            $entity
        );

        // Persist entity
        $manager->persist($entity);

        // Create reference for later usage
        $this->addReference('UserGroup-' . $this->rolesService->getShort($role), $entity);

        return true;
    }
}
