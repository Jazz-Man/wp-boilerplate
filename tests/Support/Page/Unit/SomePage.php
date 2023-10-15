<?php

declare(strict_types=1);

namespace Tests\Support\Page\Unit;

class SomePage
{
    /**
     * Declare UI map for this page here. CSS or XPath allowed.
     * public $usernameField = '#username';
     * public $formSubmitButton = "#mainForm input[type=submit]";
     */

    /**
     * @var \Tests\Support\TestUnit;
     */
    protected $testUnit;

    public function __construct(\Tests\Support\TestUnit $I)
    {
        $this->testUnit = $I;
        // you can inject other page objects here as well
    }

}
