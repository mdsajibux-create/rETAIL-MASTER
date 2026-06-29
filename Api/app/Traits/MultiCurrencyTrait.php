<?php

namespace App\Traits;
use Modules\PaymentGateways\app\Models\Currency;

trait MultiCurrencyTrait
{
    /**
     * Get fields that should be converted to user currency.
     */
    public function getCurrencyFields(): array
    {
        return property_exists($this, 'currencyFields') ? $this->currencyFields : [];
    }

    /**
     * Get the current currency from request header or default.
     */
    protected function getCurrentCurrency(): Currency
    {
        $code = request()->header('Currency');
        $currency = Currency::where('code', strtoupper($code ?? ''))
            ->where('status', true)
            ->first();

        return $currency ?? $this->getDefaultCurrency();
    }

    /**
     * Get default currency.
     */
    protected function getDefaultCurrency(): Currency
    {
        return Currency::where('is_default', true)->firstOrFail();
    }

    /**
     * Get exchange rate relative to default currency.
     */
    protected function getExchangeRate(): float
    {
        $currency = $this->getCurrentCurrency();
        $default = $this->getDefaultCurrency();

        return ($currency->exchange_rate / $default->exchange_rate) ?: 1;
    }

    /**
     * Convert a field value to the current currency.
     */
    public function getConvertedValue(string $field): float
    {
        $value = $this->attributes[$field] ?? 0;

        return round((float)$value * $this->getExchangeRate(), 2);
    }

    /**
     * Magic getter to auto-convert currency fields.
     */
    public function __get($key)
    {
        if (in_array($key, $this->getCurrencyFields())) {
            return $this->getConvertedValue($key);
        }

        return parent::__get($key);
    }
}
