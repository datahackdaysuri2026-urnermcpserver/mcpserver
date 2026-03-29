<?php

namespace Uri\Llm;

use Nemundo\Core\Base\AbstractBase;
use Nemundo\Core\Debug\Debug;
use Nemundo\Core\Json\JsonText;
use Nemundo\Core\Json\Reader\JsonReader;
use Nemundo\Core\TextFile\Writer\TextFileWriter;
use Nemundo\Core\WebRequest\BearerAuthentication\JsonBearerAuthenticationWebRequest;

class RequestLlm extends AbstractBase
{

    public $endpoint;

    public $token;

    public $message;


    public $model;

    /**
     * @var LlmFunction[]
     */
    public $toolList = [];

    public function addFunction(LlmFunction $function)
    {

        $this->toolList[] = $function;
        return $this;

    }


    public function getResponse()
    {

        $data = [];
        $data['model'] = $this->model;

        foreach ($this->toolList as $tool) {
            $data['tools'][] = $tool->getData();
        }

        $data['input'] = $this->message;

        /*$content = [];
        $content['content'] = $this->message;
        $content['role'] = 'user';
        $data['messages'][] = $content;*/

        $json = (new JsonText())->addData($data)->getJson();

        (new Debug())->write($json);

        $request = new JsonBearerAuthenticationWebRequest();
        $request->bearerAuthentication = $this->token;
        $response = $request->postUrl($this->endpoint, $json);

        $reader = new JsonReader();
        $reader->fromText($response->html);
        $data = $reader->getData();

        (new \Nemundo\Core\Debug\Debug())->write($data);


        $message = null;
        $functionCall = [];

        $outputList = $data['output'];
        foreach ($outputList as $output) {

            $type = $output['type'];

            if ($type === 'message') {
                foreach ($output['content'] as $content) {
                    $message = $content['text'];
                }

            }


            if ($type === 'function_call') {

                $functionCall[] = $output['name'] . ' ' . $output['arguments'];

            }

            //(new \Nemundo\Core\Debug\Debug())->write($output);

        }


        $response = new ResponseLlm();
        $response->message = $message;
        $response->functionList = $functionCall;

        return $response;


    }

}