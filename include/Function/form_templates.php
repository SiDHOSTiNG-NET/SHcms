<?php
/**
 * Form template helper functies voor lead websites.
 */
if (!function_exists('sh_form_templates_bootstrap')) {
    function sh_form_templates_bootstrap(): void
    {
        static $loaded = false;
        if ($loaded) {
            return;
        }
        $loaded = true;

        if (!isset($GLOBALS['sh_form_templates']) || !is_array($GLOBALS['sh_form_templates'])) {
            $GLOBALS['sh_form_templates'] = [];
        }

        $defaultFile = __DIR__ . '/../Forms/templates.default.php';
        if (is_file($defaultFile)) {
            $templates = include $defaultFile;
            if (is_array($templates)) {
                $GLOBALS['sh_form_templates'] = $templates;
            }
        }

        $customFile = __DIR__ . '/../config/forms.templates.php';
        if (is_file($customFile)) {
            $templates = include $customFile;
            if (is_array($templates)) {
                foreach ($templates as $key => $template) {
                    if (isset($GLOBALS['sh_form_templates'][$key])) {
                        $GLOBALS['sh_form_templates'][$key] = array_replace_recursive(
                            $GLOBALS['sh_form_templates'][$key],
                            $template
                        );
                    } else {
                        $GLOBALS['sh_form_templates'][$key] = $template;
                    }
                }
            }
        }
    }
}

if (!function_exists('sh_form_templates_all')) {
    function sh_form_templates_all(): array
    {
        sh_form_templates_bootstrap();
        return $GLOBALS['sh_form_templates'];
    }
}

if (!function_exists('sh_form_template')) {
    function sh_form_template(string $name): ?array
    {
        sh_form_templates_bootstrap();
        return $GLOBALS['sh_form_templates'][$name] ?? null;
    }
}

if (!function_exists('sh_register_form_template')) {
    function sh_register_form_template(string $name, array $template): void
    {
        sh_form_templates_bootstrap();
        if (isset($GLOBALS['sh_form_templates'][$name])) {
            $GLOBALS['sh_form_templates'][$name] = array_replace_recursive(
                $GLOBALS['sh_form_templates'][$name],
                $template
            );
        } else {
            $GLOBALS['sh_form_templates'][$name] = $template;
        }
    }
}

if (!function_exists('sh_render_form_template')) {
    function sh_render_form_template(string $name, array $options = []): string
    {
        $template = sh_form_template($name);
        if (!$template) {
            return '';
        }

        $method = strtoupper($options['method'] ?? ($template['method'] ?? 'POST'));
        $action = $options['action'] ?? ($template['action'] ?? $_SERVER['REQUEST_URI']);
        $formId = $options['id'] ?? ($template['id'] ?? 'form-' . $name . '-' . substr(md5($name), 0, 6));
        $formClass = trim(($template['class'] ?? '') . ' ' . ($options['class'] ?? ''));
        $submitText = $options['submit_text'] ?? ($template['submit_text'] ?? 'Verstuur');
        $submitClass = $options['submit_class'] ?? ($template['submit_class'] ?? 'btn btn-primary');
        $values = $options['values'] ?? [];
        $hiddenFields = array_merge($template['hidden'] ?? [], $options['hidden'] ?? []);
        $enctype = $options['enctype'] ?? ($template['enctype'] ?? null);
        $attributes = $options['attributes'] ?? ($template['attributes'] ?? []);

        ob_start();
        ?>
        <form method="<?php echo htmlspecialchars($method, ENT_QUOTES, 'UTF-8'); ?>"
              action="<?php echo htmlspecialchars($action, ENT_QUOTES, 'UTF-8'); ?>"
              id="<?php echo htmlspecialchars($formId, ENT_QUOTES, 'UTF-8'); ?>"
              class="<?php echo htmlspecialchars(trim($formClass), ENT_QUOTES, 'UTF-8'); ?>"
              data-form-template="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
            <?php if ($enctype) { ?>enctype="<?php echo htmlspecialchars($enctype, ENT_QUOTES, 'UTF-8'); ?>"<?php } ?>
            <?php foreach ($attributes as $attr => $value) { ?>
                <?php echo htmlspecialchars($attr, ENT_QUOTES, 'UTF-8'); ?>="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>"
            <?php } ?>
        >
            <?php if (!empty($template['intro'])) { ?>
                <p class="col-12 sh-form-template__intro"><?php echo htmlspecialchars($template['intro'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php } ?>
            <?php foreach ($hiddenFields as $hiddenName => $hiddenValue) { ?>
                <input type="hidden" name="<?php echo htmlspecialchars($hiddenName, ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo htmlspecialchars((string) $hiddenValue, ENT_QUOTES, 'UTF-8'); ?>">
            <?php } ?>
            <?php foreach ($template['fields'] as $field) { ?>
                <?php echo sh_render_form_field($field, $values); ?>
            <?php } ?>
            <div class="col-12 text-end mt-2">
                <button type="submit" class="<?php echo htmlspecialchars($submitClass, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($submitText, ENT_QUOTES, 'UTF-8'); ?>
                </button>
            </div>
        </form>
        <?php
        return trim(ob_get_clean());
    }
}

if (!function_exists('sh_render_form_field')) {
    function sh_render_form_field(array $field, array $values = []): string
    {
        $name = $field['name'] ?? '';
        if ($name === '') {
            return '';
        }

        $type = strtolower($field['type'] ?? 'text');
        $id = $field['id'] ?? 'field-' . $name . '-' . substr(md5($name), 0, 6);
        $label = $field['label'] ?? null;
        $placeholder = $field['placeholder'] ?? '';
        $required = !empty($field['required']);
        $colClass = $field['col'] ?? 'col-12';
        $help = $field['help'] ?? '';
        $inputClass = $field['input_class'] ?? '';
        $value = array_key_exists($name, $values) ? $values[$name] : ($field['value'] ?? '');
        $attributes = $field['attributes'] ?? [];
        $options = $field['options'] ?? [];
        $rows = $field['rows'] ?? 3;
        $multiple = !empty($field['multiple']);

        ob_start();
        ?>
        <div class="<?php echo htmlspecialchars($colClass, ENT_QUOTES, 'UTF-8'); ?>">
            <?php if (in_array($type, ['checkbox', 'radio'], true)) { ?>
                <div class="form-check">
                    <input type="<?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?>"
                           class="form-check-input <?php echo htmlspecialchars($inputClass, ENT_QUOTES, 'UTF-8'); ?>"
                           id="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>"
                           name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
                           value="<?php echo htmlspecialchars($field['value'] ?? '1', ENT_QUOTES, 'UTF-8'); ?>"
                        <?php if ($required) { ?>required<?php } ?>
                        <?php if (!empty($value)) { ?>checked<?php } ?>
                        <?php foreach ($attributes as $attr => $attrValue) { ?>
                            <?php echo htmlspecialchars($attr, ENT_QUOTES, 'UTF-8'); ?>="<?php echo htmlspecialchars($attrValue, ENT_QUOTES, 'UTF-8'); ?>"
                        <?php } ?>
                    >
                    <?php if ($label) { ?>
                        <label class="form-check-label" for="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                        </label>
                    <?php } ?>
                </div>
            <?php } elseif ($type === 'textarea') { ?>
                <?php if ($label) { ?>
                    <label for="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>" class="form-label">
                        <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                    </label>
                <?php } ?>
                <textarea class="form-control <?php echo htmlspecialchars($inputClass, ENT_QUOTES, 'UTF-8'); ?>"
                          id="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>"
                          name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
                          rows="<?php echo (int) $rows; ?>"
                          placeholder="<?php echo htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'); ?>"
                    <?php if ($required) { ?>required<?php } ?>
                    <?php foreach ($attributes as $attr => $attrValue) { ?>
                        <?php echo htmlspecialchars($attr, ENT_QUOTES, 'UTF-8'); ?>="<?php echo htmlspecialchars($attrValue, ENT_QUOTES, 'UTF-8'); ?>"
                    <?php } ?>
                ><?php echo htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?></textarea>
            <?php } elseif ($type === 'select') { ?>
                <?php if ($label) { ?>
                    <label for="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>" class="form-label">
                        <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                    </label>
                <?php } ?>
                <select class="form-select <?php echo htmlspecialchars($inputClass, ENT_QUOTES, 'UTF-8'); ?>"
                        id="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>"
                        name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?><?php echo $multiple ? '[]' : ''; ?>"
                    <?php if ($required) { ?>required<?php } ?>
                    <?php if ($multiple) { ?>multiple<?php } ?>
                    <?php foreach ($attributes as $attr => $attrValue) { ?>
                        <?php echo htmlspecialchars($attr, ENT_QUOTES, 'UTF-8'); ?>="<?php echo htmlspecialchars($attrValue, ENT_QUOTES, 'UTF-8'); ?>"
                    <?php } ?>
                >
                    <?php if (!empty($field['placeholder'])) { ?>
                        <option value=""><?php echo htmlspecialchars($field['placeholder'], ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php } ?>
                    <?php foreach ($options as $option) {
                        $optionValue = is_array($option) ? ($option['value'] ?? '') : $option;
                        $optionLabel = is_array($option) ? ($option['label'] ?? $optionValue) : $option;
                        $isSelected = $multiple && is_array($value)
                            ? in_array($optionValue, $value, true)
                            : (string) $value === (string) $optionValue;
                        ?>
                        <option value="<?php echo htmlspecialchars($optionValue, ENT_QUOTES, 'UTF-8'); ?>" <?php if ($isSelected) { ?>selected<?php } ?>>
                            <?php echo htmlspecialchars($optionLabel, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php } ?>
                </select>
            <?php } else { ?>
                <?php if ($label) { ?>
                    <label for="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>" class="form-label">
                        <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                    </label>
                <?php } ?>
                <input type="<?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?>"
                       class="form-control <?php echo htmlspecialchars($inputClass, ENT_QUOTES, 'UTF-8'); ?>"
                       id="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>"
                       name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
                       value="<?php echo htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?>"
                       placeholder="<?php echo htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'); ?>"
                    <?php if ($required) { ?>required<?php } ?>
                    <?php foreach ($attributes as $attr => $attrValue) { ?>
                        <?php echo htmlspecialchars($attr, ENT_QUOTES, 'UTF-8'); ?>="<?php echo htmlspecialchars($attrValue, ENT_QUOTES, 'UTF-8'); ?>"
                    <?php } ?>
                >
            <?php } ?>
            <?php if ($help) { ?>
                <div class="form-text"><?php echo htmlspecialchars($help, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php } ?>
        </div>
        <?php
        return trim(ob_get_clean());
    }
}
