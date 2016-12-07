<?php
namespace rjapi\extension\yii2\raml\ramlblocks;

use tass\extension\json\api\forms\BaseFormResourceOut;
use rjapi\extension\yii2\raml\controllers\TypesController;
use yii\console\Controller;
use yii\helpers\StringHelper;

class OutModels extends Models
{
    /** @var TypesController generator */
    private   $generator  = null;
    protected $sourceCode = '';

    private $additionalProps = [
        'id' => [
            'required' => true,
            'type'     => 'integer'
        ],
    ];

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
        $this->sourceCode = TypesController::PHP_OPEN_TAG . PHP_EOL;

        $this->sourceCode .= TypesController::PHP_NAMESPACE . ' ' . $this->generator->appDir .
                             TypesController::BACKSLASH
                             . $this->generator->modulesDir . TypesController::BACKSLASH . $this->generator->version
                             . TypesController::BACKSLASH . $this->generator->modelsFormDir .
                             TypesController::BACKSLASH . $this->generator->formsDir . TypesController::SEMICOLON
                             . PHP_EOL . PHP_EOL;

        $baseFullFormOut = BaseFormResourceOut::class;
        $baseFormOutName = StringHelper::basename($baseFullFormOut);

        $this->sourceCode .= TypesController::PHP_USE . ' ' . $baseFullFormOut . TypesController::SEMICOLON .
                             PHP_EOL . PHP_EOL;
        $this->sourceCode .= TypesController::PHP_CLASS . ' ' . TypesController::FORM_BASE .
                             TypesController::FORM_PREFIX
                             . $this->generator->objectName . TypesController::FORM_OUT . ' ' .
                             TypesController::PHP_EXTENDS
                             . ' ' . $baseFormOutName . ' ' . TypesController::OPEN_BRACE . PHP_EOL;

        if(!empty($this->generator->objectProps[TypesController::RAML_RELATIONSHIPS])
           && !empty($this->generator->types[$this->generator->objectProps[TypesController::RAML_RELATIONSHIPS]])
        )
        {
            $this->setProps(
                $this->generator->types[$this->generator->objectProps[TypesController::RAML_RELATIONSHIPS]]
                [TypesController::RAML_PROPS][TypesController::RAML_DATA][TypesController::RAML_PROPS]
            );
        }
        else
        {
            $this->setProps();
        }

        $this->constructRules();
        if(!empty($this->generator->objectProps[TypesController::RAML_RELATIONSHIPS]))
        {
            $this->constructRelations($this->generator->objectProps[TypesController::RAML_RELATIONSHIPS]);
        }
        // create closing brace
        $this->sourceCode .= PHP_EOL . TypesController::CLOSE_BRACE . PHP_EOL;

        $fileFormOut = $this->generator->rootDir . $this->generator->modulesDir . TypesController::SLASH
                       . $this->generator->version . TypesController::SLASH . $this->generator->modelsFormDir
                       . TypesController::SLASH . $this->generator->formsDir . TypesController::SLASH
                       . TypesController::FORM_BASE . TypesController::FORM_PREFIX . $this->generator->objectName
                       . TypesController::FORM_OUT . TypesController::PHP_EXT;
        FileManager::createFile($fileFormOut, $this->sourceCode);
    }

    private function setProps($relationTypes = null)
    {
        // additional props
        if(!empty($this->additionalProps))
        {
            foreach($this->additionalProps as $prop => $propVal)
            {
                $this->createProperty($prop, TypesController::PHP_MODIFIER_PUBLIC);
            }
        }

        // properties creation
        $this->sourceCode .= TypesController::TAB_PSR4 . TypesController::COMMENT . ' Attributes' . PHP_EOL;
        foreach($this->generator->types[$this->generator->objectProps[TypesController::RAML_ATTRS]]
        [TypesController::RAML_PROPS] as $propKey => $propVal)
        {
            if(is_array($propVal))
            {
                $this->createProperty($propKey, TypesController::PHP_MODIFIER_PUBLIC);
            }
        }
        $this->sourceCode .= PHP_EOL;

        // related props
        if($relationTypes !== null)
        {
            $this->sourceCode .= TypesController::TAB_PSR4 . TypesController::COMMENT . ' Relations' . PHP_EOL;
            foreach($relationTypes as $attrKey => $attrVal)
            {
                // determine attr
                if($attrKey !== TypesController::RAML_ID)
                {
                    $this->sourceCode .= TypesController::TAB_PSR4 . 'public ' . TypesController::DOLLAR_SIGN .
                                         $attrKey . TypesController::SPACE
                                         . TypesController::EQUALS . TypesController::SPACE
                                         . TypesController::PHP_TYPES_NULL . TypesController::SEMICOLON . PHP_EOL;
                }
            }
            $this->sourceCode .= PHP_EOL;
        }
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
            // determine attr
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

        $attrsCnt =
            count($this->generator->types[$this->generator->objectProps[TypesController::RAML_ATTRS]][TypesController::RAML_PROPS]);
        foreach($this->generator->types[$this->generator->objectProps[TypesController::RAML_ATTRS]]
        [TypesController::RAML_PROPS] as $attrKey => $attrVal)
        {
            --$attrsCnt;
            // determine attr
            if($attrKey !== 'type' && $attrKey !== 'required' && is_array($attrVal))
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
        $this->sourceCode .= TypesController::TAB_PSR4 . TypesController::TAB_PSR4 . TypesController::TAB_PSR4
                             . '"' . $relationTypes . '",';
    }
}