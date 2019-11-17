<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Tenant\Tenant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TenantType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'choice_loader' => new CallbackChoiceLoader(static function () {
                    return Tenant::all();
                }),
                'choice_label' => static function (Tenant $tenant) {
                    return $tenant->getDisplayName();
                },
                'choice_value' => 'id',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
