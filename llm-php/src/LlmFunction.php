<?php

namespace Uri\Llm;

use Nemundo\Core\Base\AbstractBase;

class LlmFunction extends AbstractBase
{


    public $functionName;

    public $description;

    /**
     * @var LlmParameter[]
     */
    private $parameterList = [];

    public function __construct(RequestLlm $requestLlm)
    {

        $requestLlm->addFunction($this);

    }


    public function addParameter($parameterName, $description, $type)
    {

        $parameter = new LlmParameter();
        $parameter->parameterName = $parameterName;
        $parameter->description = $description;
        $parameter->type = $type;
        $this->parameterList[] = $parameter;

        return $this;

    }


    public function getData()
    {

        $function = [];
        $function['type'] = 'function';
        $function['name'] = $this->functionName;
        $function['description'] = $this->description;

        if (sizeof($this->parameterList) > 0) {

            $function['parameters']['type'] = 'object';
            foreach ($this->parameterList as $parameter) {

                $function['parameters']['properties'][$parameter->parameterName]['type'] = $parameter->type;
                $function['parameters']['properties'][$parameter->parameterName]['description'] = $parameter->description;

            }

            $function['parameters']['required'] = [];
            $function['parameters']['additionalProperties'] = false;

        }

        $function['parameters']['strict'] = true;

        return $function;

    }

}