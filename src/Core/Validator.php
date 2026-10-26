<?php

declare(strict_types=1);

namespace Core;

class Validator
{
    private array $data;
    private array $rules;
    private array $messages;
    private array $errors = [];
    private array $customAttributes = [];

    public function __construct(array $data, array $rules, array $messages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = $messages;
    }

    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $rules) {
            $ruleList = is_array($rules) ? $rules : explode('|', $rules);
            $value = $this->getValue($field);

            foreach ($ruleList as $rule) {
                $params = [];
                if (preg_match('/^(\w+):(.+)$/', $rule, $matches)) {
                    $rule = $matches[1];
                    $params = explode(',', $matches[2]);
                }

                $method = 'validate' . ucfirst(strtolower($rule));
                if (method_exists($this, $method)) {
                    $result = $this->$method($field, $value, $params);
                    if (!$result) {
                        $this->addError($field, $rule, $params);
                        break;
                    }
                } else {
                    $this->addError($field, $rule, $params);
                }
            }
        }

        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !$this->validate();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function first(string $field): string
    {
        return $this->errors[$field][0] ?? '';
    }

    public function get(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    public function has(string $field): bool
    {
        return isset($this->errors[$field]);
    }

    public function validated(): array
    {
        $this->validate();
        $validated = [];
        foreach ($this->rules as $field => $rules) {
            $validated[$field] = $this->getValue($field);
        }
        return $validated;
    }

    public function setCustomAttributes(array $attributes): void
    {
        $this->customAttributes = $attributes;
    }

    private function getValue(string $field): mixed
    {
        $keys = explode('.', $field);
        $value = $this->data;

        foreach ($keys as $key) {
            if (is_array($value) && array_key_exists($key, $value)) {
                $value = $value[$key];
            } else {
                return null;
            }
        }

        return $value;
    }

    private function addError(string $field, string $rule, array $params): void
    {
        $attribute = $this->customAttributes[$field] ?? str_replace(['_', '.'], ' ', $field);

        $message = $this->messages["{$field}.{$rule}"]
            ?? $this->messages[$rule]
            ?? $this->getDefaultMessage($rule, $attribute, $params);

        $this->errors[$field][] = $message;
    }

    private function getDefaultMessage(string $rule, string $attribute, array $params): string
    {
        $messages = [
            'required' => 'The :attribute field is required.',
            'email' => 'The :attribute must be a valid email address.',
            'string' => 'The :attribute must be a string.',
            'numeric' => 'The :attribute must be a number.',
            'integer' => 'The :attribute must be an integer.',
            'boolean' => 'The :attribute must be true or false.',
            'array' => 'The :attribute must be an array.',
            'file' => 'The :attribute must be a file.',
            'image' => 'The :attribute must be an image.',
            'date' => 'The :attribute must be a valid date.',
            'alpha' => 'The :attribute may only contain letters.',
            'alpha_num' => 'The :attribute may only contain letters and numbers.',
            'alpha_dash' => 'The :attribute may only contain letters, numbers, dashes and underscores.',
            'min' => 'The :attribute must be at least :param characters.',
            'max' => 'The :attribute may not be greater than :param characters.',
            'between' => 'The :attribute must be between :param1 and :param2.',
            'size' => 'The :attribute must be :param kilobytes.',
            'unique' => 'The :attribute has already been taken.',
            'exists' => 'The selected :attribute is invalid.',
            'confirmed' => 'The :attribute confirmation does not match.',
            'same' => 'The :attribute and :param must match.',
            'different' => 'The :attribute and :param must be different.',
            'in' => 'The selected :attribute is invalid.',
            'not_in' => 'The selected :attribute is invalid.',
            'regex' => 'The :attribute format is invalid.',
            'ip' => 'The :attribute must be a valid IP address.',
            'url' => 'The :attribute must be a valid URL.',
            'uuid' => 'The :attribute must be a valid UUID.',
            'json' => 'The :attribute must be a valid JSON string.',
            'timezone' => 'The :attribute must be a valid timezone.',
            'before' => 'The :attribute must be a date before :param.',
            'after' => 'The :attribute must be a date after :param.',
            'digits' => 'The :attribute must have :param digits.',
            'digits_between' => 'The :attribute must have between :param1 and :param2 digits.',
            'distinct' => 'The :attribute field has a duplicate value.',
            'filled' => 'The :attribute field must have a value.',
            'nullable' => 'The :attribute field may be null.',
            'prohibited' => 'The :attribute field is prohibited.',
            'required_if' => 'The :attribute field is required when :param1 is :param2.',
            'required_unless' => 'The :attribute field is required unless :param1 is :param2.',
            'required_with' => 'The :attribute field is required when :param is present.',
            'required_with_all' => 'The :attribute field is required when all :param are present.',
            'required_without' => 'The :attribute field is required when :param is not present.',
            'required_without_all' => 'The :attribute field is required when none of :param are present.',
            'exclude_if' => 'The :attribute field is excluded when :param1 is :param2.',
            'exclude_unless' => 'The :attribute field is excluded unless :param1 is :param2.',
        ];

        $message = $messages[$rule] ?? 'The :attribute is invalid.';

        return str_replace(
            [':attribute', ':param', ':param1', ':param2'],
            [$attribute, $params[0] ?? '', $params[0] ?? '', $params[1] ?? ''],
            $message
        );
    }

    // Validation Rules

    protected function validateRequired(string $field, mixed $value): bool
    {
        if (is_array($value)) return !empty($value);
        if ($value instanceof \Stringable) return $value->__toString() !== '';
        return $value !== null && $value !== '';
    }

    protected function validateFilled(string $field, mixed $value): bool
    {
        return $this->validateRequired($field, $value);
    }

    protected function validateNullable(string $field, mixed $value): bool
    {
        return true;
    }

    protected function validateEmail(string $field, mixed $value): bool
    {
        if (!is_string($value)) return false;
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function validateString(string $field, mixed $value): bool
    {
        return is_string($value);
    }

    protected function validateNumeric(string $field, mixed $value): bool
    {
        return is_numeric($value);
    }

    protected function validateInteger(string $field, mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    protected function validateBoolean(string $field, mixed $value): bool
    {
        return is_bool($value) || in_array($value, [0, 1, '0', '1', 'true', 'false'], true);
    }

    protected function validateArray(string $field, mixed $value): bool
    {
        return is_array($value);
    }

    protected function validateFile(string $field, mixed $value): bool
    {
        return $value instanceof \SplFileInfo || (is_array($value) && isset($value['tmp_name']));
    }

    protected function validateImage(string $field, mixed $value): bool
    {
        if (!$this->validateFile($field, $value)) return false;

        $file = $value instanceof \SplFileInfo ? $value : new \SplFileInfo($value['tmp_name']);
        $mime = mime_content_type($file->getRealPath());
        return str_starts_with($mime, 'image/');
    }

    protected function validateDate(string $field, mixed $value): bool
    {
        if (!is_string($value)) return false;
        $date = \DateTime::createFromFormat('Y-m-d', $value);
        return $date !== false && $date->format('Y-m-d') === $value;
    }

    protected function validateAlpha(string $field, mixed $value): bool
    {
        return is_string($value) && preg_match('/^[\p{L}]+$/u', $value);
    }

    protected function validateAlphaNum(string $field, mixed $value): bool
    {
        return is_string($value) && preg_match('/^[\p{L}\p{N}]+$/u', $value);
    }

    protected function validateAlphaDash(string $field, mixed $value): bool
    {
        return is_string($value) && preg_match('/^[\p{L}\p{N}_-]+$/u', $value);
    }

    protected function validateMin(string $field, mixed $value, array $params): bool
    {
        $min = (int) ($params[0] ?? 0);
        if (is_string($value)) return mb_strlen($value) >= $min;
        if (is_array($value)) return count($value) >= $min;
        if (is_numeric($value)) return (float) $value >= $min;
        if ($value instanceof \SplFileInfo) return $value->getSize() >= $min * 1024;
        return false;
    }

    protected function validateMax(string $field, mixed $value, array $params): bool
    {
        $max = (int) ($params[0] ?? PHP_INT_MAX);
        if (is_string($value)) return mb_strlen($value) <= $max;
        if (is_array($value)) return count($value) <= $max;
        if (is_numeric($value)) return (float) $value <= $max;
        if ($value instanceof \SplFileInfo) return $value->getSize() <= $max * 1024;
        return false;
    }

    protected function validateBetween(string $field, mixed $value, array $params): bool
    {
        $min = (int) ($params[0] ?? 0);
        $max = (int) ($params[1] ?? PHP_INT_MAX);
        return $this->validateMin($field, $value, [$min]) && $this->validateMax($field, $value, [$max]);
    }

    protected function validateSize(string $field, mixed $value, array $params): bool
    {
        $size = (int) ($params[0] ?? 0);
        if (!$value instanceof \SplFileInfo && !(is_array($value) && isset($value['tmp_name']))) {
            return false;
        }
        $file = $value instanceof \SplFileInfo ? $value : new \SplFileInfo($value['tmp_name']);
        return $file->getSize() <= $size * 1024;
    }

    protected function validateUnique(string $field, mixed $value, array $params): bool
    {
        $table = $params[0] ?? '';
        $column = $params[1] ?? $field;
        $exceptId = $params[2] ?? null;
        $exceptColumn = $params[3] ?? 'id';

        if (!$table) return false;

        $db = Database::getInstance();
        $query = $db->table($table)->where($column, '=', $value);

        if ($exceptId !== null) {
            $query->where($exceptColumn, '!=', $exceptId);
        }

        return !$query->exists();
    }

    protected function validateExists(string $field, mixed $value, array $params): bool
    {
        $table = $params[0] ?? '';
        $column = $params[1] ?? $field;

        if (!$table) return false;

        $db = Database::getInstance();
        return $db->table($table)->where($column, '=', $value)->exists();
    }

    protected function validateConfirmed(string $field, mixed $value, array $params): bool
    {
        $confirmField = $field . '_confirmation';
        $confirmValue = $this->getValue($confirmField);
        return $value === $confirmValue;
    }

    protected function validateSame(string $field, mixed $value, array $params): bool
    {
        $otherField = $params[0] ?? '';
        $otherValue = $this->getValue($otherField);
        return $value === $otherValue;
    }

    protected function validateDifferent(string $field, mixed $value, array $params): bool
    {
        $otherField = $params[0] ?? '';
        $otherValue = $this->getValue($otherField);
        return $value !== $otherValue;
    }

    protected function validateIn(string $field, mixed $value, array $params): bool
    {
        return in_array($value, $params, true);
    }

    protected function validateNotIn(string $field, mixed $value, array $params): bool
    {
        return !in_array($value, $params, true);
    }

    protected function validateRegex(string $field, mixed $value, array $params): bool
    {
        if (!is_string($value)) return false;
        $pattern = $params[0] ?? '';
        return $pattern !== '' && preg_match($pattern, $value) === 1;
    }

    protected function validateIp(string $field, mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    protected function validateUrl(string $field, mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    protected function validateUuid(string $field, mixed $value): bool
    {
        if (!is_string($value)) return false;
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value) === 1;
    }

    protected function validateJson(string $field, mixed $value): bool
    {
        if (!is_string($value)) return false;
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }

    protected function validateTimezone(string $field, mixed $value): bool
    {
        if (!is_string($value)) return false;
        return in_array($value, \DateTimeZone::listIdentifiers(), true);
    }

    protected function validateBefore(string $field, mixed $value, array $params): bool
    {
        if (!is_string($value)) return false;
        $date = \DateTime::createFromFormat('Y-m-d', $value);
        if (!$date) return false;

        $before = $params[0] ?? date('Y-m-d');
        $beforeDate = \DateTime::createFromFormat('Y-m-d', $before);
        if (!$beforeDate) return false;

        return $date < $beforeDate;
    }

    protected function validateAfter(string $field, mixed $value, array $params): bool
    {
        if (!is_string($value)) return false;
        $date = \DateTime::createFromFormat('Y-m-d', $value);
        if (!$date) return false;

        $after = $params[0] ?? date('Y-m-d');
        $afterDate = \DateTime::createFromFormat('Y-m-d', $after);
        if (!$afterDate) return false;

        return $date > $afterDate;
    }

    protected function validateDigits(string $field, mixed $value, array $params): bool
    {
        $digits = (int) ($params[0] ?? 0);
        return is_string($value) && preg_match('/^\d{' . $digits . '}$/', $value) === 1;
    }

    protected function validateDigitsBetween(string $field, mixed $value, array $params): bool
    {
        $min = (int) ($params[0] ?? 0);
        $max = (int) ($params[1] ?? PHP_INT_MAX);
        return is_string($value) && preg_match('/^\d{' . $min . ',' . $max . '}$/', $value) === 1;
    }

    protected function validateDistinct(string $field, mixed $value): bool
    {
        if (!is_array($value)) return true;
        return count($value) === count(array_unique($value, SORT_REGULAR));
    }

    protected function validateProhibited(string $field, mixed $value): bool
    {
        return !isset($this->data[$field]);
    }

    protected function validateRequiredIf(string $field, mixed $value, array $params): bool
    {
        $otherField = $params[0] ?? '';
        $otherValue = $params[1] ?? '';
        $actualValue = $this->getValue($otherField);
        return $actualValue != $otherValue || $this->validateRequired($field, $value);
    }

    protected function validateRequiredUnless(string $field, mixed $value, array $params): bool
    {
        $otherField = $params[0] ?? '';
        $otherValue = $params[1] ?? '';
        $actualValue = $this->getValue($otherField);
        return $actualValue == $otherValue || $this->validateRequired($field, $value);
    }

    protected function validateRequiredWith(string $field, mixed $value, array $params): bool
    {
        foreach ($params as $param) {
            if ($this->getValue($param) !== null) {
                return $this->validateRequired($field, $value);
            }
        }
        return true;
    }

    protected function validateRequiredWithAll(string $field, mixed $value, array $params): bool
    {
        $allPresent = true;
        foreach ($params as $param) {
            if ($this->getValue($param) === null) {
                $allPresent = false;
                break;
            }
        }
        return !$allPresent || $this->validateRequired($field, $value);
    }

    protected function validateRequiredWithout(string $field, mixed $value, array $params): bool
    {
        foreach ($params as $param) {
            if ($this->getValue($param) === null) {
                return $this->validateRequired($field, $value);
            }
        }
        return true;
    }

    protected function validateRequiredWithoutAll(string $field, mixed $value, array $params): bool
    {
        $allMissing = true;
        foreach ($params as $param) {
            if ($this->getValue($param) !== null) {
                $allMissing = false;
                break;
            }
        }
        return !$allMissing || $this->validateRequired($field, $value);
    }

    protected function validateExcludeIf(string $field, mixed $value, array $params): bool
    {
        $otherField = $params[0] ?? '';
        $otherValue = $params[1] ?? '';
        $actualValue = $this->getValue($otherField);
        return $actualValue != $otherValue;
    }

    protected function validateExcludeUnless(string $field, mixed $value, array $params): bool
    {
        $otherField = $params[0] ?? '';
        $otherValue = $params[1] ?? '';
        $actualValue = $this->getValue($otherField);
        return $actualValue == $otherValue;
    }
}