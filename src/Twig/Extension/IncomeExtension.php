<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Customer\Domain\OperandId;
use App\Manager\SupplierManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Konstantin Grachev <me@grachevko.ru>
 */
final class IncomeExtension extends AbstractExtension
{
    private SupplierManager $supplierManager;

    public function __construct(SupplierManager $supplierManager)
    {
        $this->supplierManager = $supplierManager;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'supplier_unpaid_income',
                fn (OperandId $supplierId): array => $this->supplierManager->unpaidIncome($supplierId)
            ),
        ];
    }
}
