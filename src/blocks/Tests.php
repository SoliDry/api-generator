<?php

namespace rjapi\blocks;

use rjapi\helpers\Classes;
use rjapi\helpers\MethodOptions;
use rjapi\types\DefaultInterface;
use rjapi\types\MethodsInterface;
use rjapi\types\TestsInterface;

class Tests
{
    use ContentManager;

    private $className;

    protected $sourceCode   = '';
    protected $isSoftDelete = false;

    public function __construct($generator)
    {
        $this->generator = $generator;
        $this->className = Classes::getClassName($this->generator->objectName);
    }

    public function setContent()
    {
        $this->setTag();
        $this->startClass($this->className . DefaultInterface::FUNCTIONAL_POSTFIX);
        $methodOpts = new MethodOptions();
        $methodOpts->setName(MethodsInterface::TEST_BEFORE);
        $methodOpts->setParams([
            TestsInterface::FUNCTIONAL_TESTER => TestsInterface::PARAM_I,
        ]);
        $this->startMethod($methodOpts);
        $this->endMethod();
        $methodOpts->setName(MethodsInterface::TEST_AFTER);
        $this->startMethod($methodOpts);
        $this->endMethod();
        $this->setTestMethods($methodOpts);
        $this->endClass();
    }

    private function setTestMethods(MethodOptions $methodOpts)
    {
        $methodOpts->setName(TestsInterface::TRY . $this->generator->objectName . MethodsInterface::INDEX);
        $this->startMethod($methodOpts);
        // todo: add more methods
        $this->endMethod();
    }
}