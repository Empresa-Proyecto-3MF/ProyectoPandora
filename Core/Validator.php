<?php
/**
 * Validator simple para centralizar sanitización y reglas.
 */
class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function make(array $data): self
    {
        return new self($data);
    }

    public function required(string $field, string $msg = 'Campo requerido'): self
    {
        $v = trim((string)($this->data[$field] ?? ''));
        if ($v === '') {
            $this->errors[$field][] = $msg;
        }
        return $this;
    }

    public function email(string $field, string $msg = 'Email inválido'): self
    {
        $v = trim((string)($this->data[$field] ?? ''));
        if ($v !== '' && !filter_var($v, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = $msg;
        }
        return $this;
    }

    public function numeric(string $field, string $msg = 'Valor numérico inválido'): self
    {
        $v = $this->data[$field] ?? null;
        if ($v === null || $v === '' || !is_numeric($v)) {
            $this->errors[$field][] = $msg;
        }
        return $this;
    }

    public function min(string $field, float|int $min, string $msg = ''): self
    {
        $v = $this->data[$field] ?? null;
        if (is_numeric($v) && $v < $min) {
            $this->errors[$field][] = $msg ?: "Debe ser >= $min";
        }
        return $this;
    }

    public function max(string $field, float|int $max, string $msg = ''): self
    {
        $v = $this->data[$field] ?? null;
        if (is_numeric($v) && $v > $max) {
            $this->errors[$field][] = $msg ?: "Debe ser <= $max";
        }
        return $this;
    }

    public function minLength(string $field, int $min, string $msg = ''): self
    {
        $v = (string)($this->data[$field] ?? '');
        if (strlen($v) < $min) {
            $this->errors[$field][] = $msg ?: "Longitud mínima $min";
        }
        return $this;
    }

    public function passed(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public static function sanitizeString(string $v): string
    {
        return trim(filter_var($v, FILTER_SANITIZE_STRING));
    }
}
?>