<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Input Validator
 * 
 * Validates form input with chainable rules.
 */
class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Create a new validator instance.
     */
    public static function make(array $data): self
    {
        return new self($data);
    }

    /**
     * Validate that a field is present and not empty.
     */
    public function required(string $field, string $label = ''): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $value = $this->data[$field] ?? null;

        if ($value === null || (is_string($value) && trim($value) === '')) {
            $this->errors[$field][] = "{$label} is required.";
        }

        return $this;
    }

    /**
     * Validate that a field is a valid email.
     */
    public function email(string $field, string $label = ''): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $value = $this->data[$field] ?? '';

        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = "{$label} must be a valid email address.";
        }

        return $this;
    }

    /**
     * Validate minimum string length.
     */
    public function minLength(string $field, int $min, string $label = ''): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $value = $this->data[$field] ?? '';

        if (!empty($value) && mb_strlen($value) < $min) {
            $this->errors[$field][] = "{$label} must be at least {$min} characters.";
        }

        return $this;
    }

    /**
     * Validate maximum string length.
     */
    public function maxLength(string $field, int $max, string $label = ''): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $value = $this->data[$field] ?? '';

        if (!empty($value) && mb_strlen($value) > $max) {
            $this->errors[$field][] = "{$label} must be no more than {$max} characters.";
        }

        return $this;
    }

    /**
     * Validate that a field is numeric.
     */
    public function numeric(string $field, string $label = ''): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $value = $this->data[$field] ?? '';

        if (!empty($value) && !is_numeric($value)) {
            $this->errors[$field][] = "{$label} must be a number.";
        }

        return $this;
    }

    /**
     * Validate minimum numeric value.
     */
    public function min(string $field, float $min, string $label = ''): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $value = $this->data[$field] ?? '';

        if (!empty($value) && is_numeric($value) && (float) $value < $min) {
            $this->errors[$field][] = "{$label} must be at least {$min}.";
        }

        return $this;
    }

    /**
     * Validate maximum numeric value.
     */
    public function max(string $field, float $max, string $label = ''): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $value = $this->data[$field] ?? '';

        if (!empty($value) && is_numeric($value) && (float) $value > $max) {
            $this->errors[$field][] = "{$label} must be no more than {$max}.";
        }

        return $this;
    }

    /**
     * Validate that a field value is in a list of allowed values.
     */
    public function in(string $field, array $allowed, string $label = ''): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $value = $this->data[$field] ?? '';

        if (!empty($value) && !in_array($value, $allowed, true)) {
            $this->errors[$field][] = "{$label} contains an invalid value.";
        }

        return $this;
    }

    /**
     * Validate that two fields match (e.g. password confirmation).
     */
    public function matches(string $field, string $matchField, string $label = '', string $matchLabel = ''): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $matchLabel = $matchLabel ?: ucfirst(str_replace('_', ' ', $matchField));
        $value = $this->data[$field] ?? '';
        $matchValue = $this->data[$matchField] ?? '';

        if ($value !== $matchValue) {
            $this->errors[$field][] = "{$label} must match {$matchLabel}.";
        }

        return $this;
    }

    /**
     * Add a custom error message.
     */
    public function addError(string $field, string $message): self
    {
        $this->errors[$field][] = $message;
        return $this;
    }

    /**
     * Check if validation passed.
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Check if validation failed.
     */
    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * Get all errors.
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get the first error for a field.
     */
    public function first(string $field): string
    {
        return $this->errors[$field][0] ?? '';
    }

    /**
     * Get all errors as a flat list.
     */
    public function allErrors(): array
    {
        $flat = [];
        foreach ($this->errors as $fieldErrors) {
            foreach ($fieldErrors as $error) {
                $flat[] = $error;
            }
        }
        return $flat;
    }

    /**
     * Get a validated/sanitized value.
     */
    public function getValue(string $field, mixed $default = ''): mixed
    {
        return $this->data[$field] ?? $default;
    }

    // Static Helpers for array-based validation

    private static ?self $instance = null;

    /**
     * Statically validate data against rules array.
     */
    public static function validate(array $data, array $rules): array
    {
        $validator = self::make($data);
        
        foreach ($rules as $field => $ruleString) {
            $fieldRules = explode('|', $ruleString);
            foreach ($fieldRules as $rule) {
                if ($rule === 'required') {
                    $validator->required($field);
                } elseif ($rule === 'email') {
                    $validator->email($field);
                } elseif ($rule === 'numeric') {
                    $validator->numeric($field);
                } elseif (str_starts_with($rule, 'min:')) {
                    $min = (int) explode(':', $rule)[1];
                    // If the field is numeric, it might mean minimum value, but typically it's string length
                    $validator->minLength($field, $min);
                } elseif (str_starts_with($rule, 'match:')) {
                    $matchField = explode(':', $rule)[1];
                    $validator->matches($field, $matchField);
                }
            }
        }

        self::$instance = $validator;

        // Populate session with errors if any
        if ($validator->fails()) {
            foreach ($validator->errors() as $errField => $messages) {
                \App\Core\Session::flash('error_' . $errField, $messages[0]);
            }
        }

        return $data;
    }

    /**
     * Check if the last static validation has errors.
     */
    public static function hasErrors(): bool
    {
        return self::$instance !== null && self::$instance->fails();
    }
}

