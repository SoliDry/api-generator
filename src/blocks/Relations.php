<?php
namespace rjapi\blocks;

use rjapi\extension\json\api\forms\BaseFormResourceIn;
use rjapi\controllers\YiiTypesController;
use yii\console\Controller;
use yii\helpers\StringHelper;

class Relations extends Models
{
    /** @var YiiTypesController $generator */
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
            if(strpos($typeKey, YiiTypesController::CUSTOM_TYPES_RELATIONSHIPS) !== false)
            {
                $object           = str_replace(YiiTypesController::CUSTOM_TYPES_RELATIONSHIPS, '', $typeKey);
                $this->sourceCode = YiiTypesController::PHP_OPEN_TAG . PHP_EOL;

                $this->sourceCode .= YiiTypesController::PHP_NAMESPACE . ' ' . $this->generator->appDir .
                                     YiiTypesController::BACKSLASH . $this->generator->modulesDir .
                                     YiiTypesController::BACKSLASH . $this->generator->version .
                                     YiiTypesController::BACKSLASH . $this->generator->modelsFormDir .
                                     YiiTypesController::BACKSLASH . $this->generator->formsDir .
                                     YiiTypesController::SEMICOLON . PHP_EOL . PHP_EOL;

                $fullRelation     = BaseFormResourceIn::class;
                $baseRelationName = StringHelper::basename($fullRelation);

                $this->sourceCode .= YiiTypesController::PHP_USE . ' ' . $fullRelation . YiiTypesController::SEMICOLON .
                                     PHP_EOL . PHP_EOL;

                $this->sourceCode .= YiiTypesController::PHP_CLASS . ' ' . YiiTypesController::FORM_BASE .
                                     YiiTypesController::FORM_PREFIX . $object .
                                     YiiTypesController::FORM_IN . ' ' . YiiTypesController::PHP_EXTENDS . ' '
                                     . $baseRelationName . ' ' . YiiTypesController::OPEN_BRACE . PHP_EOL;

                $this->additionalProps = [
                    'id' => [
                        'required' => true,
                        'type'     => 'integer'
                    ],
                ];

                $objectAttrs = $object . YiiTypesController::CUSTOM_TYPES_ATTRIBUTES;
                $this->setProps($objectAttrs);
                $this->constructRules($objectAttrs);

                if(!empty($type[YiiTypesController::RAML_PROPS][YiiTypesController::RAML_DATA][YiiTypesController::RAML_PROPS]
                [YiiTypesController::RAML_RELATIONSHIPS])
                )
                {
                    $this->constructRelations(
                        $type[YiiTypesController::RAML_PROPS][YiiTypesController::RAML_DATA]
                        [YiiTypesController::RAML_PROPS][YiiTypesController::RAML_RELATIONSHIPS]
                    );
                }
                $this->sourceCode .= PHP_EOL . YiiTypesController::CLOSE_BRACE . PHP_EOL;
                $fileFormIn = $this->generator->rootDir . $this->generator->modulesDir . YiiTypesController::SLASH .
                              $this->generator->version . YiiTypesController::SLASH . $this->generator->modelsFormDir
                              . YiiTypesController::SLASH . $this->generator->formsDir . YiiTypesController::SLASH .
                              YiiTypesController::FORM_BASE . YiiTypesController::FORM_PREFIX .
                              $object . YiiTypesController::FORM_IN . YiiTypesController::PHP_EXT;
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
                $this->sourceCode .= YiiTypesController::TAB_PSR4 . 'public ' . YiiTypesController::DOLLAR_SIGN . $prop .
                                     YiiTypesController::SPACE
                                     . YiiTypesController::EQUALS . YiiTypesController::SPACE .
                                     YiiTypesController::PHP_TYPES_NULL . YiiTypesController::SEMICOLON . PHP_EOL;
            }
        }

        // properties creation
        foreach($this->generator->types[$objectAttrs][YiiTypesController::RAML_PROPS] as $attrKey => $attrVal)
        {
            $this->sourceCode .= YiiTypesController::TAB_PSR4 . 'public ' . YiiTypesController::DOLLAR_SIGN . $attrKey .
                                 YiiTypesController::SPACE
                                 . YiiTypesController::EQUALS . YiiTypesController::SPACE .
                                 YiiTypesController::PHP_TYPES_NULL . YiiTypesController::SEMICOLON . PHP_EOL;
        }
        $this->sourceCode .= PHP_EOL;
    }

    private function constructRules($objectAttrs)
    {
        $this->sourceCode .= YiiTypesController::TAB_PSR4 . 'public function rules() ' . YiiTypesController::OPEN_BRACE .
                             PHP_EOL;

        // attrs validation
        $this->sourceCode .= YiiTypesController::TAB_PSR4 . YiiTypesController::TAB_PSR4 . 'return ' .
                             YiiTypesController::OPEN_BRACKET . PHP_EOL;
        // gather required fields
        $this->setRequired($objectAttrs);
        // gather types and constraints
        $this->setTypesAndConstraints($objectAttrs);

        $this->sourceCode .= PHP_EOL . YiiTypesController::TAB_PSR4 . YiiTypesController::TAB_PSR4 .
                             YiiTypesController::CLOSE_BRACKET . YiiTypesController::SEMICOLON . PHP_EOL;
        $this->sourceCode .= YiiTypesController::TAB_PSR4 . YiiTypesController::CLOSE_BRACE;
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

        foreach($this->generator->types[$objectAttrs][YiiTypesController::RAML_PROPS] as $attrKey => $attrVal)
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
            $this->sourceCode .= YiiTypesController::TAB_PSR4 . YiiTypesController::TAB_PSR4 . YiiTypesController::TAB_PSR4 .
                                 YiiTypesController::OPEN_BRACKET . YiiTypesController::OPEN_BRACKET
                                 . $reqKeys . YiiTypesController::CLOSE_BRACKET;
            $this->sourceCode .= ', "required"';
            $this->sourceCode .= YiiTypesController::CLOSE_BRACKET;
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
                $this->sourceCode .= YiiTypesController::TAB_PSR4 . YiiTypesController::TAB_PSR4 .
                                     YiiTypesController::TAB_PSR4 . YiiTypesController::OPEN_BRACKET . '"' . $prop . '" ';
                $this->setProperty($propVal);
                $this->sourceCode .= YiiTypesController::CLOSE_BRACKET;
                $this->sourceCode .= ', ' . PHP_EOL;
            }
        }

        $attrsCnt = count($this->generator->types[$objectAttrs][YiiTypesController::RAML_PROPS]);
        foreach($this->generator->types[$objectAttrs][YiiTypesController::RAML_PROPS] as $attrKey => $attrVal)
        {
            --$attrsCnt;
            // determine attr
            if(is_array($attrVal))
            {
                $this->sourceCode .= YiiTypesController::TAB_PSR4 . YiiTypesController::TAB_PSR4 .
                                     YiiTypesController::TAB_PSR4 . YiiTypesController::OPEN_BRACKET . '"' . $attrKey .
                                     '" ';
                $this->setProperty($attrVal);
                $this->sourceCode .= YiiTypesController::CLOSE_BRACKET;
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
        $this->sourceCode .= YiiTypesController::TAB_PSR4 . 'public function relations()' . YiiTypesController::COLON .
                             ' ' . YiiTypesController::PHP_TYPES_ARRAY . ' ' . YiiTypesController::OPEN_BRACE . PHP_EOL;

        // attrs validation
        $this->sourceCode .= YiiTypesController::TAB_PSR4 . YiiTypesController::TAB_PSR4 . 'return ' .
                             YiiTypesController::OPEN_BRACKET . PHP_EOL;

        $rels = explode('|', str_replace('[]', '', $relationTypes));
        foreach($rels as $k => $rel)
        {
            $this->setRelations(strtolower(trim(str_replace(YiiTypesController::CUSTOM_TYPES_RELATIONSHIPS, '', $rel))));
            if(!empty($rels[$k + 1]))
            {
                $this->sourceCode .= PHP_EOL;
            }
        }
        $this->sourceCode .= PHP_EOL . YiiTypesController::TAB_PSR4 . YiiTypesController::TAB_PSR4 .
                             YiiTypesController::CLOSE_BRACKET . YiiTypesController::SEMICOLON . PHP_EOL;

        $this->sourceCode .= YiiTypesController::TAB_PSR4 . YiiTypesController::CLOSE_BRACE;
    }

    private function setRelations($relationTypes)
    {
        $this->sourceCode .= YiiTypesController::TAB_PSR4 . YiiTypesController::TAB_PSR4 . YiiTypesController::TAB_PSR4 .
                             '"' . $relationTypes . '",';
    }
}