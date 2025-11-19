# SHcms

## Formulier templates voor leadwebsites

Met de nieuwe form-template helper kun je snel herbruikbare formulieren tonen op verschillende leadwebsites zonder elk formulier opnieuw te bouwen.

### Beschikbare standaard templates

De map `include/Forms/templates.default.php` bevat drie direct bruikbare templates:

- `contact-basic` – basis contactformulier met naam, e-mail, telefoon en bericht.
- `quote-request` – uitgebreid formulier voor offerte-aanvragen inclusief budgetindicatie.
- `call-me-back` – kort formulier om een terugbelverzoek te verzamelen.

### Template renderen

```php
<?php
// Toon het standaard contactformulier binnen een pagina of component
echo sh_render_form_template('contact-basic', [
    'action' => '/lead/contact/submit',
    'hidden' => [
        'lead_source' => 'website-a',
    ],
]);
```

### Templates aanpassen of toevoegen

1. Kopieer `include/config/forms.templates.php.sample` naar `include/config/forms.templates.php`.
2. Voeg een nieuwe array-entry toe of overschrijf bestaande velden.

```php
<?php
return [
    'contact-basic' => [
        'submit_text' => 'Neem contact op',
        'fields' => [
            [
                'name' => 'full_name',
                'label' => 'Uw volledige naam',
            ],
        ],
    ],
    'event-signup' => [
        'label' => 'Aanmelden voor event',
        'fields' => [
            ['type' => 'text', 'name' => 'full_name', 'label' => 'Naam', 'required' => true],
            ['type' => 'email', 'name' => 'email', 'label' => 'E-mail', 'required' => true],
            ['type' => 'select', 'name' => 'session', 'label' => 'Voorkeursessie', 'options' => ['09:00', '13:00']],
        ],
        'submit_text' => 'Aanmelden',
    ],
];
```

### Runtime wijzigingen

Gebruik `sh_register_form_template()` om binnen een projectbestand dynamisch een template toe te voegen of te overschrijven:

```php
sh_register_form_template('landing-x', [
    'label' => 'Custom leadformulier',
    'fields' => [
        ['type' => 'text', 'name' => 'postcode', 'label' => 'Postcode', 'required' => true],
    ],
]);
```
