<?php

declare(strict_types=1);

namespace App\Employee\View;

use App\Customer\Domain\Operand;
use App\Customer\Domain\OperandId;
use App\Doctrine\ORM\Type\Identifier;
use App\Doctrine\Registry;
use App\Employee\Entity\EmployeeId;
use App\Infrastructure\Identifier\IdentifierFormatter;
use App\Infrastructure\Identifier\IdentifierFormatterInterface;
use function assert;
use function is_string;

final class EmployeeFormatter implements IdentifierFormatterInterface
{
    private Registry $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public static function support(): string
    {
        return EmployeeId::class;
    }

    /**
     * {@inheritdoc}
     */
    public function format(IdentifierFormatter $formatter, Identifier $identifier, string $format = null): string
    {
        $view = $this->registry->view($identifier);

        $uuid = $this->registry->connection(Operand::class)
            ->fetchColumn('SELECT uuid FROM operand WHERE id = :id', ['id' => $view['person.id']]);

        assert(is_string($uuid));

        return $formatter->format(OperandId::fromString($uuid));
    }
}