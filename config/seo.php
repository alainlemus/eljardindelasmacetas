<?php

$host = (string) parse_url((string) env('APP_URL', ''), PHP_URL_HOST);

return [

    /*
    | ¿Pueden indexar este sitio los buscadores? Producción sí; desarrollo y staging no
    | (dev.*, staging.*, *.test, localhost), para que no compitan con el sitio real.
    | Se puede forzar con SITE_INDEXABLE=true|false.
    */
    'indexable' => filter_var(
        env('SITE_INDEXABLE', ! preg_match('/^(dev|staging|stage|test)\.|\.test$|localhost|^127\./', $host)),
        FILTER_VALIDATE_BOOLEAN
    ),

    'site_name' => 'El Jardín de las Macetas',
    'description' => 'Figuras Funko Pop convertidas en macetas artesanales. Mira el catálogo de El Jardín de las Macetas y pide la tuya por WhatsApp.',
    'currency' => 'MXN',
    'locale' => 'es_MX',
];
