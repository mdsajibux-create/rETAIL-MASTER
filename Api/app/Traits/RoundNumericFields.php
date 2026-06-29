<?php

namespace App\Traits;

trait RoundNumericFields
{
    public function roundNumericFields(): array
    {
        $data = [];

        foreach ($this->getAttributes() as $key => $value) {
            if (
                is_numeric($value) &&
                $this->isFillable($key) &&
                !in_array($key, $this->excludedFieldsFromRounding ?? [], true)
            ) {
                $data[$key] = round($value); // Default: round to nearest integer
            }
        }

        return $data;
    }

    public function isFillable($key)
    {
        return in_array($key, $this->getFillable(), true);
    }

    public function applyRoundedFields(): static
    {
        $this->forceFill($this->roundNumericFields());
        return $this;
    }
}

