<?php

namespace App\Tests\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behatch\HttpCall\Request;

class BackgroundContext implements Context
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @AfterStep
     */
    public function afterStep(AfterStepScope $event): void
    {
        if (preg_match('#I send a "(.*)" request to "(.*)"#i', $event->getStep()->getText(), $matches)) {
            if (
                empty($event->getFeature())
                || empty($event->getFeature()->getBackground())
            ) {
                return;
            }
            foreach ($event->getFeature()->getBackground()->getSteps() ?? [] as $step) {
                if (preg_match('#I add "(.*)" header equal to "(.*)"#i', $step->getText(), $matches)) {
                    dump($matches[1], $matches[2]);
                    $this->request->setHttpHeader($matches[1], $matches[2]);
                }
            }
        }
    }
}
