<?php
namespace rjapi\extension\yii2\raml\ramlblocks;

use tass\extension\json\api\forms\BaseFormResourceIn;
use rjapi\extension\yii2\raml\controllers\TypesController;
use yii\console\Controller;
use yii\helpers\StringHelper;

class InModels extends Models
{
    /** @var TypesController $generator */
    private   $generator  = null;
    protected $sourceCode = '';
    private   $methods    = [
        TypesController::HTTP_METHOD_GET,
        TypesController::HTTP_METHOD_POST,
        TypesController::HTTP_METHOD_PATCH,
        TypesController::HTTP_METHOD_DELETE,
        TypesController::HTTP_METHOD_INDEX,
    ];

    private $additionalProps = [];

    public function __construct(Controller $generator)
    {
        $this->generator = $generator;
    }

    public function setCodeState(Controller $generator)
    {
        $this->generator = $generator;
    }

    public function create()
    {
        foreach($this->methods as $method)
        {
            $this->additionalProps = [];
            $this->sourceCode      = TypesController::PHP_OPEN_TAG . PHP_EOL;

            $this->sourceCode .= TypesController::PHP_NAMESPACE . ' ' . $this->generator->appDir
                                 . TypesController::BACKSLASH . $this->generator->modulesDir .
                                 TypesController::BACKSLASH
                                 . $this->generator->version . TypesController::BACKSLASH .
                                 $this->generator->modelsFormDir .
                                 TypesController::BACKSLASH . $this->generator->formsDir . TypesController::SEMICOLON
                                 . PHP_EOL . PHP_EOL;

            $fullFormInName = BaseFormResourceIn::class;
            $baseFormInName = StringHelper::basename($fullFormInName);

            $this->sourceCode .= TypesController::PHP_USE . ' ' . $fullFormInName
                                 . TypesController::SEMICOLON . PHP_EOL . PHP_EOL;

            $this->sourceCode .= TypesController::PHP_CLASS . ' ' . TypesController::FORM_PREFIX
                                 . $this->generator->objectName . TypesController::FORM_ACTION . $method
                                 . TypesController::FORM_IN . ' ' . TypesController::PHP_EXTENDS . ' ' .
                                 $baseFormInName
                                 . ' ' . TypesController::OPEN_BRACE . PHP_EOL;

            // set required id in all method but POST
            if($method !== TypesController::HTTP_METHOD_POST && $method !== TypesController::HTTP_METHOD_INDEX)
            {
                $this->additionalProps = [
                    'id' => [
                        'required' => true,
                        'type'     => 'integer'
                    ],
                ];
            }

            $this->setProps();
            $this->constructRules();

            if(!empty($this->generator->objectProps[TypesController::RAML_RELATIONSHIPS]))
            {
                $this->constructRelations($this->generator->objectProps[TypesController::RAML_RELATIONSHIPS]);
            }
            $this->sourceCode .= PHP_EOL . TypesController::CLOSE_BRACE . PHP_EOL;

            $fileFormIn = $this->generator->rootDir . $this->generator->modulesDir . TypesController::SLASH
                          . $this->generator->version . TypesController::SLASH . $this->generator->modelsFormDir
                          . TypesController::SLASH . $this->generator->formsDir . TypesController::SLASH
                          . TypesController::FORM_PREFIX . $this->generator->objectName
                          . TypesController::FORM_ACTION . $method . TypesController::FORM_IN .
                          TypesController::PHP_EXT;
            FileManager::createFile($fileFormIn, $this->sourceCode);
        }
    }

    /**
     * @internal param Method $method
     *
     * @internal param array $methods
     */
    private function setProps()
    {
        if(!empty($this->additionalProps))
        {
            foreach($this->additionalProps as $prop => $propVal)
            {
                $this->createProperty($prop, TypesController::PHP_MODIFIER_PUBLIC);
            }
        }

        // properties creation
        foreach($this->generator->types[$this->generator->objectProps[TypesController::RAML_ATTRS]]
        [TypesController::RAML_PROPS] as $attrKey => $attrVal)
        {
            $this->createProperty($attrKey, TypesController::PHP_MODIFIER_PUBLIC);
        }
        $this->sourceCode .= PHP_EOL;
    }

    private function constructRules()
    {
        $this->startMethod('rules', TypesController::PHP_MODIFIER_PUBLIC, TypesController::PHP_TYPES_ARRAY);
        // attrs validation
        $this->startArray();
        // gather required fields
        $this->setRequired();
        // gather types and constraints
        $this->setTypesAndConstraints();
        $this->endArray();
        $this->endMethod();
    }

    /**
     * @internal param Method $method
     * @internal param $attrs
     */
    private function setRequired()
    {
        $keysCnt = 0;
        $reqKeys = '';

        if(!empty($this->additionalProps))
        {
            foreach($this->additionalProps as $prop => $propVal)
            {
                if((bool) $propVal['required'] === true)
                {
                    if($keysCnt > 0)
                    {
                        $reqKeys .= ', ';
                    }
                    $reqKeys .= '"' . $prop . '"';
                    ++$keysCnt;
                }
            }
        }

        foreach($this->generator->types[$this->generator->objectProps[TypesController::RAML_ATTRS]]
        [TypesController::RAML_PROPS] as $attrKey => $attrVal)
        {
            if(is_array($attrVal))
            {
                if(isset($attrVal['required']) && (bool) $attrVal['required'] === true)
                {
                    if($keysCnt > 0)
                    {
                        $reqKeys .= ', ';
                    }
                    $reqKeys .= '"' . $attrKey . '"';
                    ++$keysCnt;
                }
            }
        }

        if($keysCnt > 0)
        {
            $this->sourceCode .= TypesController::TAB_PSR4 . TypesController::TAB_PSR4 . TypesController::TAB_PSR4
                                 . TypesController::OPEN_BRACKET . TypesController::OPEN_BRACKET
                                 . $reqKeys . TypesController::CLOSE_BRACKET;
            $this->sourceCode .= ', "required"';
            $this->sourceCode .= TypesController::CLOSE_BRACKET;
            $this->sourceCode .= ', ' . PHP_EOL;
        }
    }

    /**
     * @internal param Method $method
     * @internal param array $attrs
     */
    private function setTypesAndConstraints()
    {
        if(!empty($this->additionalProps))
        {
            foreach($this->additionalProps as $prop => $propVal)
            {
                $this->sourceCode .= TypesController::TAB_PSR4 . TypesController::TAB_PSR4 .
                                     TypesController::TAB_PSR4
                                     . TypesController::OPEN_BRACKET . '"' . $prop . '" ';
                $this->setProperty($propVal);
                $this->sourceCode .= TypesController::CLOSE_BRACKET;
                $this->sourceCode .= ', ' . PHP_EOL;
            }
        }

        $attrsCnt = count(
            $this->generator->types[$this->generator->objectProps[TypesController::RAML_ATTRS]]
            [TypesController::RAML_PROPS]
        );
        foreach($this->generator->types[$this->generator->objectProps[TypesController::RAML_ATTRS]]
        [TypesController::RAML_PROPS] as $attrKey => $attrVal)
        {
            --$attrsCnt;
            // determine attr
            if(is_array($attrVal))
            {
                $this->sourceCode .= TypesController::TAB_PSR4 . TypesController::TAB_PSR4 .
                                     TypesController::TAB_PSR4
                                     . TypesController::OPEN_BRACKET . '"' . $attrKey . '" ';
                $this->setProperty($attrVal);
                $this->sourceCode .= TypesController::CLOSE_BRACKET;
                if($attrsCnt > 0)
                {
                    $this->sourceCode .= ', ' . PHP_EOL;
                }
            }
        }
    }

    private function constructRelations($relationTypes)
    {
        $this->sourceCode .= PHP_EOL . PHP_EOL;
        $this->startMethod('relations', TypesController::PHP_MODIFIER_PUBLIC, TypesController::PHP_TYPES_ARRAY);
        // attrs validation
        $this->startArray();

        $rels = explode('|', str_replace('[]', '', $relationTypes));
        foreach($rels as $k => $rel)
        {
            $this->setRelations(strtolower(trim(str_replace(TypesController::CUSTOM_TYPES_RELATIONSHIPS, '', $rel))));
            if(!empty($rels[$k + 1]))
            {
                $this->sourceCode .= PHP_EOL;
            }
        }
        $this->endArray();
        $this->endMethod();
    }

    private function setRelations($relationTypes)
    {
        $this->sourceCode .= TypesController::TAB_PSR4 . TypesController::TAB_PSR4 . TypesController::TAB_PSR4 . '"'
                             . $relationTypes . '",';
    }
}