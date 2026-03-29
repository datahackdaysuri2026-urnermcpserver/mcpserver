<?php

require __DIR__ . '/../vendor/autoload.php';


$request = new \Uri\Llm\RequestLlm();

// hier muss ein gültiger OpenAI Api Token hinterlegt werden
$request->token = '';
$request->endpoint = 'https://api.openai.com/v1/responses';
$request->model ='gpt-5.4';



$function = new \Uri\Llm\LlmFunction($request);
$function->functionName = 'get_event';
$function->description = 'Suche nach Veranstaltungen';
$function
    ->addParameter('kategorie', 'Kategorie der Veranstaltung', 'string')
    ->addParameter('ort', 'Ortshaft des Kino', 'string')
    ->addParameter('datum', 'Datum (umwandeln in Format yyyy-mm-dd)', 'string');




//$request->message = 'mach im lehnhof eine reservierung für montag, 30. märz 2026 18 uhr für 4 personen. mein name ist kurs klang';  // 'kinoprogramm in altdorf';
//$request->message = 'kinoprogramm in altdorf von montag';
$request->message = 'gib mir sport events in altdorf von morgen';

(new \Nemundo\Core\Debug\Debug())->write($request->message);

$response = $request->getResponse();

(new \Nemundo\Core\Debug\Debug())->write($response);



