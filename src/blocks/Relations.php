<?php
namespace rjapi\blocks;

use rjapi\RJApiGenerator;
use yii\console\Controller;
use yii\helpers\StringHelper;

class Relations extends FormRequestModel
{
    /** @var RJApiGenerator $generator */
    private   $generator  = null;
    protected $sourceCode = '';

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
        foreach($this->generator->types as $typeKey => $type)
        {
            if(strpos($typeKey, RJApiGenerator::CUSTOM_TYPES_RELATIONSHIPS) !== false)
            {
                $object           = str_replace(RJApiGenerator::CUSTOM_TYPES_RELATIONSHIPS, '', $typeKey);
                $this->sourceCode = RJApiGenerator::PHP_OPEN_TAG . PHP_EOL;

                $this->sourceCode .= RJApiGenerator::PHP_NAMESPACE . ' ' . $this->generator->appDir .
                                     RJApiGenerator::BACKSLASH . $this->generator->modulesDir .
                                     RJApiGenerator::BACKSLASH . $this->generator->version .
                                     RJApiGenerator::BACKSLASH . $this->generator->modelsFormDir .
                                     RJApiGenerator::BACKSLASH . $this->generator->formsDir .
                                     RJApiGenerator::SEMICOLON . PHP_EOL . PHP_EOL;

                $fullRelation     = BaseFormResourceIn::class;
                $baseRelationName = StringHelper::basename($fullRelation);

                $this->sourceCode .= RJApiGenerator::PHP_USE . ' ' . $fullRelation . RJApiGenerator::SEMICOLON .
                                     PHP_EOL . PHP_EOL;

                $this->sourceCode .= RJApiGenerator::PHP_CLASS . ' ' . RJApiGenerator::FORM_BASE .
                                     RJApiGenerator::FORM_PREFIX . $object .
                                     RJApiGenerator::FORM_IN . ' ' . RJApiGenerator::PHP_EXTENDS . ' '
                                     . $baseRelationName . ' ' . RJApiGenerator::OPEN_BRACE . PHP_EOL;

                $this->additionalProps = [
                    'id' => [
                        'required' => true,
                        'type'     => 'integer'
                    ],
                ];

                $objectAttrs = $object . RJApiGenerator::CUSTOM_TYPES_ATTRIBUTES;
                $this->setProps($objectAttrs);
                $this->constructRules($objectAttrs);

                if(!empty($type[RJApiGenerator::RAML_PROPS][RJApiGenerator::RAML_DATA][RJApiGenerator::RAML_PROPS]
                [RJApiGenerator::RAML_RELATIONSHIPS])
                )
                {
                    $this->constructRelations(
                        $type[RJApiGenerator::RAML_PROPS][RJApiGenerator::RAML_DATA]
                        [RJApiGenerator::RAML_PROPS][RJApiGenerator::RAML_RELATIONSHIPS]
                    );
                }
                $this->sourceCode .= PHP_EOL . RJApiGenerator::CLOSE_BRACE . PHP_EOL;
                $fileFormIn = $this->generator->rootDir . $this->generator->modulesDir . RJApiGenerator::SLASH .
                              $this->generator->version . RJApiGenerator::SLASH . $this->generator->modelsFormDir
                              . RJApiGenerator::SLASH . $this->generator->formsDir . RJApiGenerator::SLASH .
                              RJApiGenerator::FORM_BASE . RJApiGenerator::FORM_PREFIX .
                              $object . RJApiGenerator::FORM_IN . RJApiGenerator::PHP_EXT;
                FileManager::createFile($fileFormIn, $this->sourceCode);
            }
        }
    }

    /**
     * @internal param Method $method
     *
     * @internal param array $methods
     */
    private function setProps($objectAttrs)
    {
        if(!empty($this->additionalProps))
        {
            foreach($this->additionalProps as $prop => $propVal)
            {
                $this->sourceCode .= RJApiGenerator::TAB_PSR4 . 'public ' . RJApiGenerator::DOLLAR_SIGN . $prop .
                                     RJApiGenerator::SPACE
                                     . RJApiGenerator::EQUALS . RJApiGenerator::SPACE .
                                     RJApiGenerator::PHP_TYPES_NULL . RJApiGenerator::SEMICOLON . PHP_EOL;
            }
        }

        // properties creation
        foreach($this->generator->types[$objectAttrs][RJApiGenerator::RAML_PROPS] as $attrKey => $attrVal)
        {
            $this->sourceCode .= RJApiGenerator::TAB_PSR4 . 'public ' . RJApiGenerator::DOLLAR_SIGN . $attrKey .
                                 RJApiGenerator::SPACE
                                 . RJApiGenerator::EQUALS . RJApiGenerator::SPACE .
                                 RJApiGenerator::PHP_TYPES_NULL . RJApiGenerator::SEMICOLON . PHP_EOL;
        }
        $this->sourceCode .= PHP_EOL;
    }

    private function constructRules($objectAttrs)
    {
        $this->sourceCode .= RJApiGenerator::TAB_PSR4 . 'public function rules() ' . RJApiGenerator::OPEN_BRACE .
                             PHP_EOL;

        // attrs validation
        $this->sourceCode .= RJApiGenerator::TAB_PSR4 . RJApiGenerator::TAB_PSR4 . 'return ' .
                             RJApiGenerator::OPEN_BRACKET . PHP_EOL;
        // gather required fields
        $this->setRequired($objectAttrs);
        // gather types and constraints
        $this->setTypesAndConstraints($objectAttrs);

        $this->sourceCode .= PHP_EOL . RJApiGenerator::TAB_PSR4 . RJApiGenerator::TAB_PSR4 .
                             RJApiGenerator::CLOSE_BRACKET . RJApiGenerator::SEMICOLON . PHP_EOL;
        $this->sourceCode .= RJApiGenerator::TAB_PSR4 . RJApiGenerator::CLOSE_BRACE;
    }

    /**
     * @internal param Method $method
     * @internal param $attrs
     */
    private function setRequired($objectAttrs)
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

        foreach($this->generator->types[$objectAttrs][RJApiGenerator::RAML_PROPS] as $attrKey => $attrVal)
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
            $this->sourceCode .= RJApiGenerator::TAB_PSR4 . RJApiGenerator::TAB_PSR4 . RJApiGenerator::TAB_PSR4 .
                                 RJApiGenerator::OPEN_BRACKET . RJApiGenerator::OPEN_BRACKET
                                 . $reqKeys . RJApiGenerator::CLOSE_BRACKET;
            $this->sourceCode .= ', "required"';
            $this->sourceCode .= RJApiGenerator::CLOSE_BRACKET;
            $this->sourceCode .= ', ' . PHP_EOL;
        }
    }

    /**
     * @internal param Method $method
     * @internal param array $attrs
     */
    private function setTypesAndConstraints($objectAttrs)
    {
        if(!empty($this->additionalProps))
        {
            foreach($this->additionalProps as $prop => $propVal)
            {
                $this->sourceCode .= RJApiGenerator::TAB_PSR4 . RJApiGenerator::TAB_PSR4 .
                                     RJApiGenerator::TAB_PSR4 . RJApiGenerator::OPEN_BRACKET . '"' . $prop . '" ';
                $this->setPropertyFilters($propVal);
                $this->sourceCode .= RJApiGenerator::CLOSE_BRACKET;
                $this->sourceCode .= ', ' . PHP_EOL;
            }
        }

        $attrsCnt = count($this->generator->types[$objectAttrs][RJApiGenerator::RAML_PROPS]);
        foreach($this->generator->types[$objectAttrs][RJApiGenerator::RAML_PROPS] as $attrKey => $attrVal)
        {
            --$attrsCnt;
            // determine attr
            if(is_array($attrVal))
            {
                $this->sourceCode .= RJApiGenerator::TAB_PSR4 . RJApiGenerator::TAB_PSR4 .
                                     RJApiGenerator::TAB_PSR4 . RJApiGenerator::OPEN_BRACKET . '"' . $attrKey .
                                     '" ';
                $this->setPropertyFilters($attrVal);
                $this->sourceCode .= RJApiGenerator::CLOSE_BRACKET;
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
        $this->sourceCode .= RJApiGenerator::TAB_PSR4 . 'public function relations()' . RJApiGenerator::COLON .
                             ' ' . RJApiGenerator::PHP_TYPES_ARRAY . ' ' . RJApiGenerator::OPEN_BRACE . PHP_EOL;

        // attrs validation
        $this->sourceCode .= RJApiGenerator::TAB_PSR4 . RJApiGenerator::TAB_PSR4 . 'return ' .
                             RJApiGenerator::OPEN_BRACKET . PHP_EOL;

        $rels = explode('|', str_replace('[]', '', $relationTypes));
        foreach($rels as $k => $rel)
        {
            $this->setRelations(strtolower(trim(str_replace(RJApiGenerator::CUSTOM_TYPES_RELATIONSHIPS, '', $rel))));
            if(!empty($rels[$k + 1]))
            {
                $this->sourceCode .= PHP_EOL;
            }
        }
        $this->sourceCode .= PHP_EOL . RJApiGenerator::TAB_PSR4 . RJApiGenerator::TAB_PSR4 .
                             RJApiGenerator::CLOSE_BRACKET . RJApiGenerator::SEMICOLON . PHP_EOL;

        $this->sourceCode .= RJApiGenerator::TAB_PSR4 . RJApiGenerator::CLOSE_BRACE;
    }

    private function setRelations($relationTypes)
    {
        $this->sourceCode .= RJApiGenerator::TAB_PSR4 . RJApiGenerator::TAB_PSR4 . RJApiGenerator::TAB_PSR4 .
                             '"' . $relationTypes . '",';
    }
}