<?php

declare(strict_types=1);

namespace App\Manager;

use App\Customer\Domain\Operand;
use App\Customer\Domain\OperandId;
use App\Doctrine\Registry;
use App\Income\Entity\Income;

/**
 * @author Konstantin Grachev <me@grachevko.ru>
 */
final class SupplierManager
{
    private Registry $registry;

    private PaymentManager $paymentManager;

    public function __construct(Registry $registry, PaymentManager $paymentManager)
    {
        $this->registry = $registry;
        $this->paymentManager = $paymentManager;
    }

    /**
     * @return array<string, Income>
     */
    public function unpaidIncome(OperandId $supplierId): array
    {
        $balance = $this->paymentManager->balance(
            $this->registry->findBy(Operand::class, ['uuid' => $supplierId])
        );

        if (!$balance->isPositive()) {
            return [];
        }

        /** @var Income[] $incomes */
        $incomes = $this->registry->repository(Income::class)
            ->createQueryBuilder('entity')
            ->where('entity.supplierId = :supplier')
            ->andWhere('entity.accruedAt IS NOT NULL')
            ->orderBy('entity.accruedAt', 'DESC')
            ->addOrderBy('entity.id', 'DESC')
            ->getQuery()
            ->setMaxResults(10)
            ->setParameter('supplier', $supplierId)
            ->getResult();

        $result = [];
        foreach ($incomes as $income) {
            if (!$balance->isPositive()) {
                break;
            }

            $balance = $balance->subtract($income->getAccruedAmount());

            $result[$income->toId()->toString()] = $income;
        }

        return $result;
    }
}
