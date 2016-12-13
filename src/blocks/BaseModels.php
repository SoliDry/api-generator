<?php
namespace rjapi\blocks;

use rjapi\extension\json\api\forms\BaseFormResource;
use rjapi\controllers\YiiRJApiGenerator;
use yii\console\Controller;
use yii\helpers\StringHelper;

class BaseModels extends Models
{
    use ContentManager;

    protected $sourceCode = '';
    /** @var YiiRJApiGenerator generator */
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
            YiiRJApiGenerator::BACKSLASH . $this->generator->formsDir
        );

        $baseFullForm = BaseFormResource::class;
        $baseFormName = StringHelper::basename($baseFullForm);
        $this->setUse($baseFullForm);
        $this->startClass(
            YiiRJApiGenerator::FORM_BASE .
            YiiRJApiGenerator::FORM_PREFIX . $this->generator->objectName, $baseFormName
        );

        if(!empty($this->generator->objectProps[YiiRJApiGenerator::RAML_RELATIONSHIPS][YiiRJApiGenerator::RAML_TYPE])
           &&
           !empty($this->generator->types[$this->generator->objectProps[YiiRJApiGenerator::RAML_RELATIONSHIPS][YiiRJApiGenerator::RAML_TYPE]])
        )
        {
            $this->setProps(
                $this->generator->types[$this->generator->objectProps[YiiRJApiGenerator::RAML_RELATIONSHIPS][YiiRJApiGenerator::RAML_TYPE]]
                [YiiRJApiGenerator::RAML_PROPS][YiiRJApiGenerator::RAML_DATA][YiiRJApiGenerator::RAML_ITEMS]
            );
        }
        else
        {
            $this->setProps();
        }

        $this->constructRules();
        if(!empty($this->generator->objectProps[YiiRJApiGenerator::RAML_RELATIONSHIPS]))
        {
            $this->constructRelations($this->generator->objectProps[YiiRJApiGenerator::RAML_RELATIONSHIPS]);
        }
        // create closing brace
        $this->endClass();

        $fileForm = $this->generator->formatFormsPath()
                    . YiiRJApiGenerator::SLASH
                    . YiiRJApiGenerator::FORM_BASE
                    . YiiRJApiGenerator::FORM_PREFIX
                    . $this->generator->objectName
                    . YiiRJApiGenerator::PHP_EXT;
        FileManager::createFile($fileForm, $this->sourceCode);
    }

    private function setProps($relationTypes = null)
    {
        // additional props
        if(!empty($this->additionalProps))
        {
            foreach($this->additionalProps as $prop => $propVal)
            {
                $this->createProperty($prop, YiiRJApiGenerator::PHP_MODIFIER_PUBLIC);
            }
        }

        // properties creation
        $this->sourceCode .= YiiRJApiGenerator::TAB_PSR4 . YiiRJApiGenerator::COMMENT . ' Attributes' . PHP_EOL;
        foreach($this->generator->types[$this->generator->objectProps[YiiRJApiGenerator::RAML_ATTRS]]
        [YiiRJApiGenerator::RAML_PROPS] as $propKey => $propVal)
        {
            if(is_array($propVal))
            {
                $this->createProperty($propKey, YiiRJApiGenerator::PHP_MODIFIER_PUBLIC);
            }
        }
        $this->sourceCode .= PHP_EOL;

        // related props
        if($relationTypes !== null)
        {
            $this->sourceCode .= YiiRJApiGenerator::TAB_PSR4 . YiiRJApiGenerator::COMMENT . ' Relations' . PHP_EOL;
            foreach($relationTypes as $attrKey => $attrVal)
            {
                // determine attr
                if($attrKey !== YiiRJApiGenerator::RAML_ID && $attrKey !== YiiRJApiGenerator::RAML_TYPE)
                {
                    $this->createProperty($attrKey, YiiRJApiGenerator::PHP_MODIFIER_PUBLIC);
                }
            }
            $this->sourceCode .= PHP_EOL;
        }
    }

    private function constructRules()
    {
        $this->startMethod(YiiRJApiGenerator::PHP_YII_RULES, YiiRJApiGenerator::PHP_MODIFIER_PUBLIC, YiiRJApiGenerator::PHP_TYPES_ARRAY);
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
                if(empty($propVal[YiiRJApiGenerator::RAML_REQUIRED]) === false &&
                   (bool) $propVal[YiiRJApiGenerator::RAML_REQUIRED] === true
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

        foreach($this->generator->types[$this->generator->objectProps[YiiRJApiGenerator::RAML_ATTRS]]
        [YiiRJApiGenerator::RAML_PROPS] as $attrKey => $attrVal)
        {
            // determine attr
            if(is_array($attrVal))
            {
                if(isset($attrVal[YiiRJApiGenerator::RAML_REQUIRED]) &&
                   (bool) $attrVal[YiiRJApiGenerator::RAML_REQUIRED] === true
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
            $this->sourceCode .= YiiRJApiGenerator::TAB_PSR4 . YiiRJApiGenerator::TAB_PSR4 . YiiRJApiGenerator::TAB_PSR4
                                 . YiiRJApiGenerator::OPEN_BRACKET . YiiRJApiGenerator::OPEN_BRACKET
                                 . $reqKeys . YiiRJApiGenerator::CLOSE_BRACKET;
            $this->sourceCode .= ', "' . YiiRJApiGenerator::RAML_REQUIRED . '"';
            $this->sourceCode .= YiiRJApiGenerator::CLOSE_BRACKET;
            $this->sourceCode .= ', ' . PHP_EOL;
        }
    }

    private function setTypesAndConstraints()
    {
        if(!empty($this->additionalProps))
        {
            foreach($this->additionalProps as $prop => $propVal)
            {
                $this->sourceCode .= YiiRJApiGenerator::TAB_PSR4 . YiiRJApiGenerator::TAB_PSR4 .
                                     YiiRJApiGenerator::TAB_PSR4
                                     . YiiRJApiGenerator::OPEN_BRACKET . '"' . $prop . '" ';
                $this->setProperty($propVal);
                $this->sourceCode .= YiiRJApiGenerator::CLOSE_BRACKET;
                $this->sourceCode .= ', ' . PHP_EOL;
            }
        }

        $attrsCnt =
            count($this->generator->types[$this->generator->objectProps[YiiRJApiGenerator::RAML_ATTRS]][YiiRJApiGenerator::RAML_PROPS]);
        foreach($this->generator->types[$this->generator->objectProps[YiiRJApiGenerator::RAML_ATTRS]]
        [YiiRJApiGenerator::RAML_PROPS] as $attrKey => $attrVal)
        {
            --$attrsCnt;
            // determine attr
            if($attrKey !== YiiRJApiGenerator::RAML_TYPE && $attrKey !== YiiRJApiGenerator::RAML_REQUIRED &&
               is_array($attrVal)
            )
            {
                $this->sourceCode .= YiiRJApiGenerator::TAB_PSR4 . YiiRJApiGenerator::TAB_PSR4 .
                                     YiiRJApiGenerator::TAB_PSR4
                                     . YiiRJApiGenerator::OPEN_BRACKET . '"' . $attrKey . '" ';
                $this->setProperty($attrVal);
                $this->sourceCode .= YiiRJApiGenerator::CLOSE_BRACKET;
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
        $this->startMethod(YiiRJApiGenerator::PHP_YII_RELATIONS, YiiRJApiGenerator::PHP_MODIFIER_PUBLIC, YiiRJApiGenerator::PHP_TYPES_ARRAY);
        // attrs validation
        $this->startArray();
        $rel = empty($relationTypes[YiiRJApiGenerator::RAML_TYPE]) ? $relationTypes :
            $relationTypes[YiiRJApiGenerator::RAML_TYPE];

        $rels = explode('|', str_replace('[]', '', $rel));
        foreach($rels as $k => $rel)
        {
            $this->setRelations(strtolower(trim(str_replace(YiiRJApiGenerator::CUSTOM_TYPES_RELATIONSHIPS, '', $rel))));
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
        $this->sourceCode .= YiiRJApiGenerator::TAB_PSR4 . YiiRJApiGenerator::TAB_PSR4 . YiiRJApiGenerator::TAB_PSR4
                             . '"' . $relationTypes . '",';
    }
}