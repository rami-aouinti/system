<?php

declare(strict_types=1);

namespace App\User\Transport\Command\User;

use App\General\Transport\Command\Traits\SymfonyStyleTrait;
use App\User\Application\Resource\UserGroupResource;
use App\User\Domain\Entity\User;
use App\User\Domain\Entity\UserGroup;
use Closure;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

use function array_map;
use function implode;
use function sprintf;

/**
 * @package App\User
 */
#[AsCommand(
    name: self::NAME,
    description: 'Console command to list user groups',
)]
class ListUserGroupsCommand extends Command
{
    use SymfonyStyleTrait;

    final public const string NAME = 'user:list-groups';

    /**
     * @throws LogicException
     */
    public function __construct(
        private readonly UserGroupResource $userGroupResource,
    ) {
        parent::__construct();
    }

    /**
     * @noinspection PhpMissingParentCallCommonInspection
     *
     * {@inheritdoc}
     *
     * @throws Throwable
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getSymfonyStyle($input, $output);
        $headers = [
            'Id',
            'Name',
            'Role',
            'Users',
        ];
        $io->title('Current user groups');
        $io->table($headers, $this->getRows());

        return 0;
    }

    /**
     * Getter method for formatted user group rows for console table.
     *
     * @throws Throwable
     *
     * @return array<int, string>
     */
    private function getRows(): array
    {
        return array_map($this->getFormatterUserGroup(), $this->userGroupResource->find(orderBy: [
            'name' => 'ASC',
        ]));
    }

    /**
     * Getter method for user group formatter closure. This closure will format single UserGroup entity for console
     * table.
     */
    private function getFormatterUserGroup(): Closure
    {
        $userFormatter = static fn (User $user): string => sprintf(
            '%s %s <%s>',
            $user->getFirstName(),
            $user->getLastName(),
            $user->getEmail(),
        );

        return static fn (UserGroup $userGroup): array => [
            $userGroup->getId(),
            $userGroup->getName(),
            $userGroup->getRole()->getId(),
            implode(",\n", $userGroup->getUsers()->map($userFormatter)->toArray()),
        ];
    }
}
