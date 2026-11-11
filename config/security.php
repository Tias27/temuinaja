<?php

return [
    'allowed_hosts' => array_filter(array_map('trim', explode(',', env(
        'APP_ALLOWED_HOSTS',
        'localhost,127.0.0.1,::1,temuin.test',
    )))),
];
