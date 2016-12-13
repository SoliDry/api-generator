<?php
namespace rjapi\blocks;

use rjapi\extension\json\api\forms\BaseFormResourceIn;
use rjapi\controllers\YiiRJApiGenerator;
use yii\console\Controller;
use yii\helpers\StringHelper;

class Relations extends Models
{
    /** @var YiiRJApiGenerator $generator */
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
            if(strpos($typeKey, YiiRJApiGenerator::CUSTOM_TYPES_RELATIONSHIPS) !== false)
            {
                $object           = str_replace(YiiRJApiGenerator::CUSTOM_TYPES_RELATIONSHIPS, '', $typeKey);
                $this->sourceCode = YiiRJApiGenerator::PHP_OPEN_TAG . PHP_EOL;

                $this->sourceCode .= YiiRJApiGenerator::PHP_NAMESPACE . ' ' . $this->generator->appDir .
                                     YiiRJApiGenerator::BACKSLASH . $this->generator->modulesDir .
                                     YiiRJApiGenerator::BACKSLASH . $this->generator->version .
                                     YiiRJApiGenerator::BACKSLASH . $this->generator->modelsFormDir .
                                     YiiRJApiGenerator::BACKSLASH . $this->generator->formsDir .
                                     YiiRJApiGenerator::SEMICOLON . PHP_EOL . PHP_EOL;

                $fullRelation     = BaseFormResourceIn::class;
                $baseRelationName = StringHelper::basename($fullRelation);

                $this->sourceCode .= YiiRJApiGenerator::PHP_USE . ' ' . $fullRelation . YiiRJApiGenerator::SEMICOLON .
                                     PHP_EOL . PHP_EOL;

                $this->sourceCode .= YiiRJApiGenerator::PHP_CLASS . ' ' . YiiRJApiGenerator::FORM_BASE .
                                     YiiRJApiGenerator::FORM_PREFIX . $object .
                                     YiiRJApiGenerator::FORM_IN . ' ' . YiiRJApiGenerator::PHP_EXTENDS . ' '
                                     . $baseRelationName . ' ' . YiiRJApiGenerator::OPEN_BRACE . PHP_EOL;

                $this->additionalProps = [
                    'id' => [
                        'required' => true,
                        'type'     => 'integer'
                    ],
                ];

                $objectAttrs = $object . YiiRJApiGenerator::CUSTOM_TYPES_ATTRIBUTES;
                $this->setProps($objectAttrs);
                $this->constructRules($objectAttrs);

                if(!empty($type[YiiRJApiGenerator::RAML_PROPS][YiiRJApiGenerator::RAML_DATA][YiiRJApiGenerator::RAML_PROPS]
                [YiiRJApiGenerator::RAML_RELATIONSHIPS])
                )
                {
                    $this->constructRelations(
                        $type[YiiRJApiGenerator::RAML_PROPS][YiiRJApiGenerator::RAML_DATA]
                        [YiiRJApiGenerator::RAML_PROPS][YiiRJApiGenerator::RAML_RELATIONSHIPS]
                    );
                }
                $this->sourceCode .= PHP_EOL . YiiRJApiGenerator::CLOSE_BRACE . PHP_EOL;
                $fileFormIn = $this->generator->rootDir . $this->generator->modulesDir . YiiRJApiGenerator::SLASH .
                              $this->generator->version . YiiRJApiGenerator::SLASH . $this->generator->modelsFormDir
                              . YiiRJApiGenerator::SLASH . $this->generator->formsDir . YiiRJApiGenerator::SLASH .
                              YiiRJApiGenerator::FORM_BASE . YiiRJApiGenerator::FORM_PREFIX .
                              $object . YiiRJApiGenerator::FORM_IN . YiiRJApiGenerator::PHP_EXT;
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
                $this->sourceCode .= YiiRJApiGenerator::TAB_PSR4 . 'public ' . YiiRJApiGenerator::DOLLAR_SIGN . $prop .
                                     YiiRJApiGenerator::SPACE
                                     . YiiRJApiGenerator::EQUALS . YiiRJApiGenerator::SPACE .
                                     YiiRJApiGenerator::PHP_TYPES_NULL . YiiRJApiGenerator::SEMICOLON . PHP_EOL;
            }
        }

        // properties creation
        foreach($this->generator->types[$objectAttrs][YiiRJApiGenerator::RAML_PROPS] as $attrKey => $attrVal)
        {
            $this->sourceCode .= YiiRJApiGenerator::TAB_PSR4 . 'public ' . YiiRJApiGenerator::DOLLAR_SIGN . $attrKey .
                                 YiiRJApiGenerator::SPACE
                                 . YiiRJApiGenerator::EQUALS . YiiRJApiGenerator::SPACE .
                                 YiiRJApiGenerator::PHP_TYPES_NULL . YiiRJApiGenerator::SEMICOLON . PHP_EOL;
        }
        $this->sourceCode .= PHP_EOL;
    }

    private function constructRules($objectAttrs)
    {
        $this->sourceCode .= YiiRJApiGenerator::TAB_PSR4 . 'public function rules() ' . YiiRJApiGenerator::OPEN_BRACE .
                             PHP_EOL;

        // attrs validation
        $this->sourceCode .= YiiRJApiGenerator::TAB_PSR4 . YiiRJApiGenerator::TAB_PSR4 . 'return ' .
                             YiiRJApiGenerator::OPEN_BRACKET . PHP_EOL;
        // gather required fields
        $this->setRequired($objectAttrs);
        // gather types and constraints
        $this->setTypesAndConstraints($objectAttrs);

        $this->sourceCode .= PHP_EOL . YiiRJApiGenerator::TAB_PSR4 . YiiRJApiGenerator::TAB_PSR4 .
                             YiiRJApiGenerator::CLOSE_BRACKET . YiiRJApiGenerator::SEMICOLON . PHP_EOL;
        $this->sourceCode .= YiiRJApiGenerator::TAB_PSR4 . YiiRJApiGenerator::CLOSE_BRACE;
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

        foreach($this->generator->types[$objectAttrs][YiiRJApiGenerator::RAML_PROPS] as $attrKey => $attrVal)
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
            $this->sourceCode .= YiiRJApiGenerator::TAB_PSR4 . YiiRJApiGenerator::TAB_PSR4 . YiiRJApiGenerator::TAB_PSR4 .
                                 YiiRJApiGenerator::OPEN_BRACKET . YiiRJApiGenerator::OPEN_BRACKET
                                 . $reqKeys . YiiRJApiGenerator::CLOSE_BRACKET;
            $this->sourceCode .= ', "required"';
            $this->sourceCode .= YiiRJApiGenerator::CLOSE_BRACKET;
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
                $this->sourceCode .= YiiRJApiGenerator::TAB_PSR4 . YiiRJApiGenerator::TAB_PSR4 .
                                     YiiRJApiGenerator::TAB_PSR4 . YiiRJApiGenerator::OPEN_BRACKET . '"' . $prop . '" ';
                $this->setProperty($propVal);
                $this->sourceCode .= YiiRJApiGenerator::CLOSE_BRACKET;
                $this->sourceCode .= ', ' . PHP_EOL;
            }
        }

        $attrsCnt = count($this->generator->types[$objectAttrs][YiiRJApiGenerator::RAML_PROPS]);
        foreach($this->generator->types[$objectAttrs][YiiRJApiGenerator::RAML_PROPS] as $attrKey => $attrVal)
        {
            --$attrsCnt;
            // determine attr
            if(is_array($attrVal))
            {
                $this->sourceCode .= YiiRJApiGenerator::TAB_PSR4 . YiiRJApiGenerator::TAB_PSR4 .
                                     YiiRJApiGenerator::TAB_PSR4 . YiiRJApiGenerator::OPEN_BRACKET . '"' . $attrKey .
                                     '" ';
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
        $this->sourceCode .= YiiRJApiGenerator::TAB_PSR4 . 'public function relations()' . YiiRJApiGenerator::COLON .
                             ' ' . YiiRJApiGenerator::PHP_TYPES_ARRAY . ' ' . YiiRJApiGenerator::OPEN_BRACE . PHP_EOL;

        // attrs validation
        $this->sourceCode .= YiiRJApiGenerator::TAB_PSR4 . YiiRJApiGenerator::TAB_PSR4 . 'return ' .
                             YiiRJApiGenerator::OPEN_BRACKET . PHP_EOL;

        $rels = explode('|', str_replace('[]', '', $relationTypes));
        foreach($rels as $k => $rel)
        {
            $this->setRelations(strtolower(trim(str_replace(YiiRJApiGenerator::CUSTOM_TYPES_RELATIONSHIPS, '', $rel))));
            if(!empty($rels[$k + 1]))
            {
                $this->sourceCode .= PHP_EOL;
            }
        }
        $this->sourceCode .= PHP_EOL . YiiRJApiGenerator::TAB_PSR4 . YiiRJApiGenerator::TAB_PSR4 .
                             YiiRJApiGenerator::CLOSE_BRACKET . YiiRJApiGenerator::SEMICOLON . PHP_EOL;

        $this->sourceCode .= YiiRJApiGenerator::TAB_PSR4 . YiiRJApiGenerator::CLOSE_BRACE;
    }

    private function setRelations($relationTypes)
    {
        $this->sourceCode .= YiiRJApiGenerator::TAB_PSR4 . YiiRJApiGenerator::TAB_PSR4 . YiiRJApiGenerator::TAB_PSR4 .
                             '"' . $relationTypes . '",';
    }
}