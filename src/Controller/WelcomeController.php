<?php

namespace Slam\Controller;

class WelcomeController
{
    protected $body = <<<BODY
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>Welcome to Slam</title>
        <style>
            body {
                font-family: 'Helvetica', sans-serif;
            }
        </style>
    </head>
    <body>
        <h1>Slam</h1>
        <p>You've gotten Slam running!</p>
    </body>
</html>
BODY;

    public function __invoke($request, $response, $args)
    {
        $response->getBody()
            ->write($this->body);
            
        return $response;
    }
}
