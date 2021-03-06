<?php

declare(strict_types=1);

namespace App\Form\Transformer;

use function ctype_digit;
use function is_int;
use function is_numeric;
use function is_string;
use function number_format;
use function str_replace;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @author Konstantin Grachev <me@grachevko.ru>
 */
final class DivisoredNumberTransformer implements DataTransformerInterface
{
    private int $divisor;

    private int $scale;

    public function __construct(int $divisor = 100, int $scale = 2)
    {
        $this->divisor = $divisor;
        $this->scale = $scale;
    }

    /**
     * {@inheritDoc}
     */
    public function transform($value): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!is_int($value) && !ctype_digit($value)) {
            throw new TransformationFailedException('Integer expected.');
        }

        return number_format((int) $value / $this->divisor, $this->scale, ',', '');
    }

    /**
     * {@inheritDoc}
     */
    public function reverseTransform($value): ?int
    {
        if (null === $value) {
            return null;
        }

        if (is_string($value)) {
            $value = str_replace(',', '.', $value);
        }

        if (!is_numeric($value)) {
            throw new TransformationFailedException('Numeric expected.');
        }

        $value = ((float) $value) * $this->divisor;

        return (int) (string) $value;
    }
}
