<?php

namespace rjapi\blocks;

use Faker\Factory;
use rjapi\helpers\Classes;
use rjapi\helpers\MethodOptions;
use rjapi\types\DefaultInterface;
use rjapi\types\MethodsInterface;
use rjapi\types\PhpInterface;
use rjapi\types\ApiInterface;
use rjapi\types\TestsInterface;

class Tests
{
    use ContentManager;

    private $className;
    private $attributesState = [];

    protected $sourceCode   = '';
    protected $isSoftDelete = false;

    public function __construct($generator)
    {
        $this->generator = $generator;
        $this->className = Classes::getClassName($this->generator->objectName);
    }

    public function setContent() : void
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
        // main test methods
        $this->collectProps();
        $this->setComment(DefaultInterface::METHOD_START);
        $this->setCreateContent($methodOpts);
        $this->setIndexContent($methodOpts);
        $this->setViewContent($methodOpts);
        $this->setUpdateContent($methodOpts);
        $this->setDeleteContent($methodOpts);
        $this->setComment(DefaultInterface::METHOD_END);
        $this->endClass();
    }

    /**
     * @param MethodOptions $methodOpts
     */
    private function setIndexContent(MethodOptions $methodOpts) : void
    {
        $methodOpts->setName(TestsInterface::TRY . $this->generator->objectName . ucfirst(MethodsInterface::INDEX));
        $this->startMethod($methodOpts);
        $this->methodCallOnObject(TestsInterface::PARAM_I, TestsInterface::IM_GOING_TO,
            [TestsInterface::TEST_WORD . PhpInterface::SPACE . $this->generator->objectName . ' ' . MethodsInterface::INDEX]);
        $this->methodCallOnObject(TestsInterface::PARAM_I, TestsInterface::SEND_GET,
            [PhpInterface::SLASH . $this->generator->version . PhpInterface::SLASH . mb_strtolower($this->generator->objectName)]);
        $this->methodCallOnObject(TestsInterface::PARAM_I, TestsInterface::SEE_RESP_IS_JSON);
        $this->methodCallOnObject(TestsInterface::PARAM_I, TestsInterface::SEE_RESP_CONTAINS_JSON, [$this->getJsonApiResponse(true)], false);
        $this->endMethod();
    }

    /**
     * @param MethodOptions $methodOpts
     */
    private function setViewContent(MethodOptions $methodOpts) : void
    {
        $id = $id = $this->getId();
        $methodOpts->setName(TestsInterface::TRY . $this->generator->objectName . ucfirst(MethodsInterface::VIEW));
        $this->startMethod($methodOpts);
        $this->methodCallOnObject(TestsInterface::PARAM_I, TestsInterface::IM_GOING_TO,
            [TestsInterface::TEST_WORD . PhpInterface::SPACE . $this->generator->objectName
             . PhpInterface::SPACE . MethodsInterface::VIEW]);
        $this->methodCallOnObject(TestsInterface::PARAM_I, TestsInterface::SEND_GET,
            [PhpInterface::SLASH . $this->generator->version . PhpInterface::SLASH . mb_strtolower($this->generator->objectName)
             . PhpInterface::SLASH . $id]);
        $this->methodCallOnObject(TestsInterface::PARAM_I, TestsInterface::SEE_RESP_IS_JSON);
        $this->methodCallOnObject(TestsInterface::PARAM_I, TestsInterface::SEE_RESP_CONTAINS_JSON, [$this->getJsonApiResponse(true)], false);
        $this->endMethod();
    }

    /**
     * @param MethodOptions $methodOpts
     */
    private function setCreateContent(MethodOptions $methodOpts) : void
    {
        $methodOpts->setName(TestsInterface::TRY . $this->generator->objectName . ucfirst(MethodsInterface::CREATE));
        $this->startMethod($methodOpts);
        $this->methodCallOnObject(TestsInterface::PARAM_I, TestsInterface::IM_GOING_TO,
            [TestsInterface::TEST_WORD . PhpInterface::SPACE . $this->generator->objectName
             . PhpInterface::SPACE . MethodsInterface::CREATE]);
        $this->methodCallOnObject(TestsInterface::PARAM_I, TestsInterface::SEND_POST,
            [PhpInterface::SLASH . $this->generator->version . PhpInterface::SLASH . mb_strtolower($this->generator->objectName),
             $this->getJsonApiRequest()]);
        $this->methodCallOnObject(TestsInterface::PARAM_I, TestsInterface::SEE_RESP_IS_JSON);
        $this->methodCallOnObject(TestsInterface::PARAM_I, TestsInterface::SEE_RESP_CONTAINS_JSON, [$this->getJsonApiResponse(true)], false);
        $this->endMethod();
    }

    /**
     * @param MethodOptions $methodOpts
     */
    private function setUpdateContent(MethodOptions $methodOpts) : void
    {
        $id = $id = $this->getId();
        $methodOpts->setName(TestsInterface::TRY . $this->generator->objectName . ucfirst(MethodsInterface::UPDATE));
        $this->startMethod($methodOpts);
        $this->methodCallOnObject(TestsInterface::PARAM_I, TestsInterface::IM_GOING_TO,
            [TestsInterface::TEST_WORD . PhpInterface::SPACE . $this->generator->objectName
             . PhpInterface::SPACE . MethodsInterface::UPDATE]);
        $this->methodCallOnObject(TestsInterface::PARAM_I, TestsInterface::SEND_PATCH,
            [PhpInterface::SLASH . $this->generator->version . PhpInterface::SLASH . mb_strtolower($this->generator->objectName)
             . PhpInterface::SLASH . $id, $this->getJsonApiRequest()]);
        $this->methodCallOnObject(TestsInterface::PARAM_I, TestsInterface::SEE_RESP_IS_JSON);
        $this->methodCallOnObject(TestsInterface::PARAM_I, TestsInterface::SEE_RESP_CONTAINS_JSON, [$this->getJsonApiResponse(true)], false);
        $this->endMethod();
    }

    /**
     * @param MethodOptions $methodOpts
     */
    private function setDeleteContent(MethodOptions $methodOpts) : void
    {
        $id = $this->getId();
        if (empty($this->attributesState[ApiInterface::RAML_ID]) === false) {
            $id = $this->attributesState[ApiInterface::RAML_ID];
        }
        $methodOpts->setName(TestsInterface::TRY . $this->generator->objectName . ucfirst(MethodsInterface::DELETE));
        $this->startMethod($methodOpts);
        $this->methodCallOnObject(TestsInterface::PARAM_I, TestsInterface::IM_GOING_TO,
            [TestsInterface::TEST_WORD . PhpInterface::SPACE . $this->generator->objectName
             . PhpInterface::SPACE . MethodsInterface::DELETE]);
        $this->methodCallOnObject(TestsInterface::PARAM_I, TestsInterface::SEND_DELETE,
            [PhpInterface::SLASH . $this->generator->version . PhpInterface::SLASH . mb_strtolower($this->generator->objectName) . PhpInterface::SLASH . $id]);
        $this->endMethod();
    }

    private function getId()
    {
        if (empty($this->attributesState[ApiInterface::RAML_ID]) === false) {
            return $this->attributesState[ApiInterface::RAML_ID];
        }
        return 1;
    }

    /**
     * @param bool $required
     * @return array
     */
    private function getJsonApiRequest($required = false) : array
    {
        $attrs = [];
        // set id if it's string
        if (empty($this->attributesState[ApiInterface::RAML_ID]) === false) {
            $attrs[ApiInterface::RAML_ID] = $this->attributesState[ApiInterface::RAML_ID];
        }
        $props = $this->generator->types[$this->generator->objectProps[ApiInterface::RAML_ATTRS]][ApiInterface::RAML_PROPS];
        foreach ($this->attributesState as $attrKey => $attrVal) {
            if ($required === false || ($required === true && empty($props[$attrKey][ApiInterface::RAML_KEY_REQUIRED]) === false)) {
                $attrs[$attrKey] = $attrVal;
            }
        }
        return [
            ApiInterface::RAML_DATA => [
                ApiInterface::RAML_TYPE  => mb_strtolower($this->generator->objectName),
                ApiInterface::RAML_ATTRS => $attrs,
            ],
        ];
    }

    /**
     * @param bool $required
     * @return array
     */
    private function getJsonApiResponse($required = false) : array
    {
        $attrs = [];
        $props = $this->generator->types[$this->generator->objectProps[ApiInterface::RAML_ATTRS]][ApiInterface::RAML_PROPS];
        foreach ($this->attributesState as $attrKey => $attrVal) {
            if ($required === false || ($required === true && empty($props[$attrKey][ApiInterface::RAML_KEY_REQUIRED]) === false)) {
                $attrs[$attrKey] = $attrVal;
            }
        }
        return [
            ApiInterface::RAML_DATA => [
                ApiInterface::RAML_TYPE  => mb_strtolower($this->generator->objectName),
                ApiInterface::RAML_ID    => $this->getId(),
                ApiInterface::RAML_ATTRS => $attrs,
            ],
        ];
    }

    private function collectProps()
    {
        $idObject = $this->generator->types[$this->generator->types[$this->generator->objectName][ApiInterface::RAML_PROPS][ApiInterface::RAML_ID]];
        if ($idObject[ApiInterface::RAML_TYPE] === ApiInterface::RAML_TYPE_STRING) {
            $this->attributesState[ApiInterface::RAML_ID] = uniqid();
        }
        $props = $this->generator->types[$this->generator->objectProps[ApiInterface::RAML_ATTRS]][ApiInterface::RAML_PROPS];
        foreach ($props as $attrKey => $attrVal) {
            $this->attributesState[$attrKey] = $this->getAttributeValue($attrVal);
        }
    }

    /**
     * @param array $attrVal
     * @return mixed
     */
    private function getAttributeValue(array $attrVal)
    {
        $faker = Factory::create();
        $type  = !empty($attrVal[ApiInterface::RAML_ENUM]) ? ApiInterface::RAML_ENUM : $attrVal[ApiInterface::RAML_TYPE];
        switch ($type) {
            case ApiInterface::RAML_TYPE_NUMBER:
            case ApiInterface::RAML_TYPE_INTEGER:
                return $faker->randomDigit;
            case ApiInterface::RAML_TYPE_STRING:
                return $faker->userName;
            case ApiInterface::RAML_TYPE_BOOLEAN:
                return $faker->boolean;
            case ApiInterface::RAML_ENUM:
                return $attrVal[ApiInterface::RAML_ENUM][0];
            case ApiInterface::RAML_DATE:
                return $faker->date();
            case ApiInterface::RAML_TIME:
                return $faker->time();
            case ApiInterface::RAML_DATETIME:
                return $faker->dateTime()->format('Y-m-d H:i:s');
        }
        return $faker->name;
    }
}