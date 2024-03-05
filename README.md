# Form Security

Librería PHP para filtrar y validar los campos de un formulario al ser recibidos en el servidor.

## Características

- Prevención de ataques CSRF

## Instalación

```sh
composer require cygnus-uy/form-security
```

## Ejemplo

```php
<?php

use CygnusUy\FormSecurity\FormChecker;
use CygnusUy\FormSecurity\HandlerCSRFAttack;

require 'vendor/autoload.php';

$formChecker = new FormChecker([]);
$formChecker->addCheckerHandler(
    HandlerCSRFAttack::class,
    new HandlerCSRFAttack(null, [
        'namespace' => 'creatierra_',
        'tokenId' => 'token_',
    ])
);
$requiredEntries = $formChecker->getRequiredEntries();

```
