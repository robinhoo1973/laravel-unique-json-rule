<?php
namespace TopviewDigital\UniqueJsonRule;

use DB;

class UniqueJsonValidator
{
    /**
     * Check if the translated value is unique in the database.
     *
     * @param string $attribute
     * @param string $value
     * @param array $parameters
     * @param \Illuminate\Validation\Validator $validator
     *
     * @return bool
     */
    public function validate($attribute, $value, array $parameters, $validator)
    {
        list($name, $json_field) = array_map('trim', explode('.', $attribute));
        $parameters = array_map('trim', $parameters);
        $parameters = array_map(function ($u) {
            return strtolower($u) == 'null' || empty($u) ? null : $u;
        }, $parameters);
        list($table, $combined_fields, $except_value, $id_field) = array_pad($parameters, 4, null);
        list($field, $json) = array_pad(
            array_filter(explode('->', $combined_fields), 'strlen'),
            2,
            null
        );
        $field = $field ?: $name;
        $json = $json ?? $json_field;

        $findJsonValue = $this->findJsonValue(
            $value,
            $json,
            $table,
            $field,
            $except_value,
            $id_field
        );
        if (!$findJsonValue) {
            $this->addErrorsToValidator($validator, $parameters, $name, $json_field);
        }
        return $findJsonValue;
    }

    /**
     * Check if a translation is unique.
     *
     * @param mixed $value
     * @param string $locale
     * @param string $table
     * @param string $column
     * @param mixed $ignoreValue
     * @param string|null $ignoreColumn
     *
     * @return bool
     */
    protected function findJsonValue(
        $value,
        $json,
        $table,
        $field,
        $except_value,
        $id_field
    ) {
        $except_value = $except_value ?? null;
        $id_field = $id_field ?? 'id';
        // dd($value, $json, $table, $field, $except_value, $id_field);
        $query = DB::table($table)->where("{$field}->{$json}", $value);
        if ($except_value) {
            $query = $query->where($id_field, "!=", $except_value);
        }

        return $query->count() === 0;
    }

    /**
     * Add error messages to the validator.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @param array $parameters
     * @param string $name
     * @param string $locale
     *
     * @return void
     */
    protected function addErrorsToValidator($validator, $parameters, $name, $json_field)
    {
        $rule = 'unique_json';
        $message = $this->getFormattedMessage($validator, $rule, $parameters, $name, $json_field);
        $validator->errors()
            ->add($name, $message)
            ->add("{$name}.{$json_field}", $message);
    }
    /**
     * Get the formatted error message.
     *
     * This will format the placeholders:
     * e.g. "post_slug" will become "post slug".
     *
     * @param \Illuminate\Validation\Validator $validator
     * @param string $rule
     * @param array $parameters
     * @param string $name
     * @param string $locale
     *
     * @return string
     */
    protected function getFormattedMessage($validator, $rule, $parameters, $name, $json_field)
    {
        $message = $this->getMessage($validator, $rule, $name, $json_field);
        return $validator->makeReplacements($message, $name, $rule, $parameters);
    }
    /**
     * Get any custom message from the validator or return a default message.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @param string $rule
     * @param string $name
     * @param string $locale
     *
     * @return string
     */
    protected function getMessage($validator, $rule, $name, $json_field)
    {
        $keys = [
            "{$name}.{$rule}",
            "{$name}.*.{$rule}",
            "{$name}.{$json_field}.{$rule}",
        ];
        foreach ($keys as $key) {
            if (array_key_exists($key, $validator->customMessages)) {
                return $validator->customMessages[$key];
            }
        }
        return trans('validation.unique');
    }
}
