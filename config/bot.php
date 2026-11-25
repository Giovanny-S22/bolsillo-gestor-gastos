<?php

// Dirección del servidor Rasa (canal REST). Se inicia con: rasa run --port 5005
return [
    'rasa_url' => 'http://127.0.0.1:5005/webhooks/rest/webhook',
    'timeout'   => 15,   // segundos de espera a Rasa
    'max_texto' => 500,  // máximo de caracteres por mensaje
];
