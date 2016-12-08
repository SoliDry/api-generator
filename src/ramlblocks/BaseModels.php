<?php
namespace rjapi\extension\yii2\raml\ramlblocks;

use rjapi\extension\json\api\forms\BaseFormResource;
use rjapi\extension\yii2\raml\controllers\TypesController;
use yii\console\Controller;
use yii\helpers\StringHelper;

class BaseModels extends Models
{
    use ContentManager;

    protected $sourceCode = '';
    /** @var TypesController generator */
    private $generator       = null;
    private $additionalProps = [
        'id' => [
            'type' => 'integer',
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
        $this->setTag();
        $this->setNamespace(
            $this->generator->modelsFormDir .
            TypesController::BACKSLASH . $this->generator->formsDir
        );

        $baseFullForm = BaseFormResource::class;
        $baseFormName = StringHelper::basename($baseFullForm);
        $this->setUse($baseFullForm);
        $this->startClass(
            TypesController::FORM_BASE .
            TypesController::FORM_PREFIX . $this->generator->objectName, $baseFormName
        );

        if(!empty($this->generator->objectProps[TypesController::RAML_RELATIONSHIPS][TypesController::RAML_TYPE])
           &&
           !empty($this->generator->types[$this->generator->objectProps[TypesController::RAML_RELATIONSHIPS][TypesController::RAML_TYPE]])
        )
        {
            $this->setProps(
                $this->generator->types[$this->generator->objectProps[TypesController::RAML_RELATIONSHIPS][TypesController::RAML_TYPE]]
                [TypesController::RAML_PROPS][TypesController::RAML_DATA][TypesController::RAML_ITEMS]
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
        $this->endClass();

        $fileForm = $this->generator->formatFormsPath()
                    . TypesController::SLASH
                    . TypesController::FORM_BASE
                    . TypesController::FORM_PREFIX
                    . $this->generator->objectName
                    . TypesController::PHP_EXT;
        FileManager::createFile($fileForm, $this->sourceCode);
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
                if($attrKey !== TypesController::RAML_ID && $attrKey !== TypesController::RAML_TYPE)
                {
                    $this->createProperty($attrKey, TypesController::PHP_MODIFIER_PUBLIC);
                }
            }
            $this->sourceCode .= PHP_EOL;
        }
    }

    private function constructRules()
    {
        $this->startMethod(TypesController::PHP_YII_RULES, TypesController::PHP_MODIFIER_PUBLIC, TypesController::PHP_TYPES_ARRAY);
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
                if(empty($propVal[TypesController::RAML_REQUIRED]) === false &&
                   (bool) $propVal[TypesController::RAML_REQUIRED] === true
                )
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
                if(isset($attrVal[TypesController::RAML_REQUIRED]) &&
                   (bool) $attrVal[TypesController::RAML_REQUIRED] === true
                )
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
            $this->sourceCode .= ', "' . TypesController::RAML_REQUIRED . '"';
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
            if($attrKey !== TypesController::RAML_TYPE && $attrKey !== TypesController::RAML_REQUIRED &&
               is_array($attrVal)
            )
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
        $this->startMethod(TypesController::PHP_YII_RELATIONS, TypesController::PHP_MODIFIER_PUBLIC, TypesController::PHP_TYPES_ARRAY);
        // attrs validation
        $this->startArray();
        $rel = empty($relationTypes[TypesController::RAML_TYPE]) ? $relationTypes :
            $relationTypes[TypesController::RAML_TYPE];

        $rels = explode('|', str_replace('[]', '', $rel));
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