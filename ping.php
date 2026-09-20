<?php
// Endpoint ultra-léger pour garder le serveur Render éveillé sans consommer de ressources
header('Content-Type: text/plain; charset=utf-8');
http_response_code(200);
echo "pong";
