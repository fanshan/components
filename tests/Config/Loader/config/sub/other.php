<?php

    use ObjectivePHP\Config\Config;

    $otherConfig = new Config([
        'app.env'         => 'prod',
        'packages.loaded' => 'sub'
    ]);

    $extra = (new Config());

    $extra->package->pre->fromArray([
        'version' => '0.1b'

    ]);

    return $otherConfig->merge($extra);