<?php

namespace App\Tests\Behat\Context;

use Behat\Behat\Context\Context;
use Behatch\HttpCall\HttpCallResultPool;
use Behatch\Json\Json;
use Behatch\Json\JsonInspector;
use Ramsey\Uuid\Uuid;

class JsonContext implements Context
{
    protected JsonInspector $inspector;
    protected HttpCallResultPool $httpCallResultPool;

    public function __construct(HttpCallResultPool $httpCallResultPool, string $evaluationMode = 'javascript')
    {
        $this->inspector = new JsonInspector($evaluationMode);
        $this->httpCallResultPool = $httpCallResultPool;
    }

    /**
     * @Given the JSON node :jsonNode
     */
    public function theJsonNode(string $jsonNode)
    {
        $json = $this->getJson();

        return $this->inspector->evaluate($json, $jsonNode);
    }

    /**
     * @Then the JSON node :node should be a valid JWT
     */
    public function theJsonNodeShouldBeAJWT(string $node)
    {
        $json = $this->getJson();

        $jwt = $this->inspector->evaluate($json, $node);

        if (Uuid::isValid($jwt)) {
            throw new \Exception(
                sprintf("The node '%s' is not a valid JWT", $jwt)
            );
        }
    }

    protected function getJson(): Json
    {
        return new Json($this->httpCallResultPool->getResult()->getValue());
    }
}
