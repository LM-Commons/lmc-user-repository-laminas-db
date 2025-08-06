<?php

declare(strict_types=1);

namespace Lmc\User\Repository\Db\Adapter;

use Laminas\Hydrator\HydratorInterface;
use Lmc\User\Repository\UserInterface as UserEntityInterface;

use function assert;

final readonly class UserHydrator implements HydratorInterface
{
    public function __construct(
        private HydratorInterface $hydrator,
        private string $idColumn = 'id',
    ) {
    }

    /**
     * Extract values from an object
     */
    public function extract(object $object): array
    {
        assert($object instanceof UserEntityInterface);

        $data = $this->hydrator->extract($object);
        // identity is a construct that does not map to a column
        unset($data['identity']);
        return $this->mapField('id', $this->idColumn, $data);
    }

    /**
     * Hydrate $object with the provided $data.
     */
    public function hydrate(array $data, object $object): UserEntityInterface
    {
        assert($object instanceof UserEntityInterface);

        $data = $this->mapField($this->idColumn, 'id', $data);

        return $this->hydrator->hydrate($data, $object);
    }

    /** @psalm-suppress MixedAssignment */
    protected function mapField(string $keyFrom, string $keyTo, array $array): array
    {
        if (isset($array[$keyFrom])) {
            $array[$keyTo] = $array[$keyFrom];
            if ($keyTo !== $keyFrom) {
                unset($array[$keyFrom]);
            }
        }

        return $array;
    }
}
