<?php

namespace SoliDry\Blocks;

use SoliDry\Controllers\BaseCommand;
use SoliDry\Extension\BaseFormRequest;
use SoliDry\Extension\JSONApiInterface;
use SoliDry\Helpers\Console;
use SoliDry\Helpers\MethodOptions;
use SoliDry\ApiGenerator;
use SoliDry\Helpers\Classes;
use SoliDry\Helpers\MigrationsHelper;
use SoliDry\Types\ConfigInterface;
use SoliDry\Types\CustomsInterface;
use SoliDry\Types\DefaultInterface;
use SoliDry\Types\DirsInterface;
use SoliDry\Types\HTTPMethodsInterface;
use SoliDry\Types\MethodsInterface;
use SoliDry\Types\FromRequestInterface;
use SoliDry\Types\PhpInterface;
use SoliDry\Types\ApiInterface;

/**
 * Class FormRequest
 *
 * @package SoliDry\Blocks
 * @property ApiGenerator generator
 */
class FormRequest extends FormRequestModel
{
    use ContentManager;

    protected $sourceCode = '';
    protected $resourceCode = '';
    protected $generator;
    private $additionalProps = [
        'id' => [
            'type' => 'integer',
        ],
    ];
    private $className;

    /**
     * FormRequest constructor.
     * @param BaseCommand $generator
     */
    public function __construct(BaseCommand $generator)
    {
        $this->generator = $generator;
        $this->className = Classes::getClassName($this->generator->objectName);
    }

    /**
     * @param $generator
     */
    public function setCodeState($generator)
    {
        $this->generator = $generator;
    }

    /**
     * @param null $relationTypes
     */
    private function setProps($relationTypes = null)
    {
        $this->setAdditionalProps();
        // properties creation
        $this->setPropsContent();
        // related props
        $this->setRelationTypes($relationTypes);
    }

    /**
     *  Sets an additional props e.g.: id
     */
    private function setAdditionalProps(): void
    {
        // additional props
        if (!empty($this->additionalProps)) {
            foreach ($this->additionalProps as $prop => $propVal) {
                $this->createProperty($prop, PhpInterface::PHP_MODIFIER_PUBLIC);
            }
        }
    }

    /**
     *  Sets props values
     */
    private function setPropsContent(): void
    {
        $this->setComment(CustomsInterface::CUSTOM_TYPES_ATTRIBUTES);

        /** @var array $objectProps */
        $objectProps = $this->generator->types[$this->generator->objectProps[ApiInterface::RAML_ATTRS]][ApiInterface::RAML_PROPS];
        foreach ($objectProps as $propKey => $propVal) {

            if (is_array($propVal)) {
                $this->createProperty($propKey, PhpInterface::PHP_MODIFIER_PUBLIC);

                if (empty($propVal[ApiInterface::RAML_FACETS][ConfigInterface::BIT_MASK]) === false) {
                    $this->setComment(ConfigInterface::BIT_MASK);

                    foreach ($propVal[ApiInterface::RAML_FACETS][ConfigInterface::BIT_MASK] as $flag => $bit) {
                        $this->createProperty($flag, PhpInterface::PHP_MODIFIER_PUBLIC, $bit);
                    }
                }
            }
        }
    }

    /**
     * Sets relation types
     *
     * @param $relationTypes
     */
    private function setRelationTypes($relationTypes): void
    {
        // related props
        if ($relationTypes !== null) {
            $this->sourceCode .= PhpInterface::TAB_PSR4 . PhpInterface::COMMENT . ' Relations' . PHP_EOL;

            foreach ($relationTypes as $attrKey => $attrVal) {
                // determine attr
                if ($attrKey !== ApiInterface::RAML_ID && $attrKey !== ApiInterface::RAML_TYPE) {
                    $this->createProperty($attrKey, PhpInterface::PHP_MODIFIER_PUBLIC);
                }
            }
            $this->sourceCode .= PHP_EOL;
        }
    }

    /**
     *  Sets all rules for this FormRequest
     */
    private function constructRules(): void
    {
        // Authorize method - defaults to false
        $methodOptions = new MethodOptions();
        $methodOptions->setName(PhpInterface::PHP_AUTHORIZE);
        $methodOptions->setReturnType(PhpInterface::PHP_TYPES_BOOL);
        $this->startMethod($methodOptions);
        $this->setMethodReturn(PhpInterface::PHP_TYPES_BOOL_TRUE);
        $this->endMethod();

        // Rules method
        $methodOptions->setName(PhpInterface::PHP_RULES);
        $methodOptions->setReturnType(PhpInterface::PHP_TYPES_ARRAY);
        $this->startMethod($methodOptions);
        // attrs validation
        $this->startArray();
        // gather Types and constraints
        $this->setPropertyFilters();
        $this->endArray();
        $this->endMethod();
    }

    /**
     * Sets all relations for Entity via FormRequests
     *
     * @param $relationTypes
     */
    private function constructRelations($relationTypes): void
    {
        $methodOptions = new MethodOptions();
        $methodOptions->setName(MethodsInterface::RELATIONS);
        $methodOptions->setReturnType(PhpInterface::PHP_TYPES_ARRAY);
        $this->startMethod($methodOptions);
        // attrs validation
        $this->startArray();
        if (empty($relationTypes) === false) {
            $rel = empty($relationTypes[ApiInterface::RAML_TYPE]) ? $relationTypes :
                $relationTypes[ApiInterface::RAML_TYPE];

            $rels = explode(PhpInterface::PIPE, str_replace('[]', '', $rel));
            foreach ($rels as $k => $rel) {
                $this->setRelations(MigrationsHelper::getTableName(trim(str_replace(CustomsInterface::CUSTOM_TYPES_RELATIONSHIPS, '', $rel))));
                if (empty($rels[$k + 1]) === false) {
                    $this->sourceCode .= PHP_EOL;
                }
            }
        }
        $this->endArray();
        $this->endMethod(1);
    }

    /**
     * @param $relationTypes
     */
    private function setRelations($relationTypes): void
    {
        $this->setTabs(3);
        $this->sourceCode .= PhpInterface::QUOTES . $relationTypes . PhpInterface::QUOTES . PhpInterface::COMMA;
    }

    /**
     *  Sets content of *FormRequest
     */
    private function setContent(): void
    {
        $this->setTag();
        $this->setNamespace(
            $this->generator->httpDir .
            PhpInterface::BACKSLASH .
            $this->generator->formRequestDir
        );

        $baseFullForm = BaseFormRequest::class;
        $baseFormName = Classes::getName($baseFullForm);
        $this->setUse($baseFullForm, false, true);
        $this->startClass($this->className . DefaultInterface::FORM_REQUEST_POSTFIX, $baseFormName);

        $this->setComment(DefaultInterface::PROPS_START);
        if (empty($this->generator->objectProps[ApiInterface::RAML_RELATIONSHIPS][ApiInterface::RAML_TYPE]) === false
            && empty($this->generator->types[$this->generator->objectProps[ApiInterface::RAML_RELATIONSHIPS][ApiInterface::RAML_TYPE]]) === false) {
            $this->setProps(
                $this->generator->types[$this->generator->objectProps[ApiInterface::RAML_RELATIONSHIPS][ApiInterface::RAML_TYPE]]
                [ApiInterface::RAML_PROPS][ApiInterface::RAML_DATA][ApiInterface::RAML_ITEMS]
            );
        } else {
            $this->setProps();
        }
        $this->setComment(DefaultInterface::PROPS_END);
        $this->sourceCode .= PHP_EOL;
        $this->setComment(DefaultInterface::METHOD_START);
        $this->constructRules();
        $relTypes = empty($this->generator->objectProps[ApiInterface::RAML_RELATIONSHIPS][ApiInterface::RAML_TYPE])
            ? [] : $this->generator->objectProps[ApiInterface::RAML_RELATIONSHIPS][ApiInterface::RAML_TYPE];
        $this->constructRelations($relTypes);
        $this->setComment(DefaultInterface::METHOD_END);
        // create closing brace
        $this->endClass();
    }

    /**
     *  Sets content of *FormRequest
     */
    private function resetContent(): void
    {
        $this->setBeforeProps($this->getEntityFile($this->generator->formatRequestsPath(),
            DefaultInterface::FORM_REQUEST_POSTFIX));
        $this->setComment(DefaultInterface::PROPS_START, 0);
        if (empty($this->generator->objectProps[ApiInterface::RAML_RELATIONSHIPS][ApiInterface::RAML_TYPE]) === false
            && empty($this->generator->types[$this->generator->objectProps[ApiInterface::RAML_RELATIONSHIPS][ApiInterface::RAML_TYPE]]) === false) {
            $this->setProps(
                $this->generator->types[$this->generator->objectProps[ApiInterface::RAML_RELATIONSHIPS][ApiInterface::RAML_TYPE]]
                [ApiInterface::RAML_PROPS][ApiInterface::RAML_DATA][ApiInterface::RAML_ITEMS]
            );
        } else {
            $this->setProps();
        }
        $this->setAfterProps(DefaultInterface::METHOD_START);
        $this->setComment(DefaultInterface::METHOD_START, 0);
        $this->constructRules();
        $relTypes = empty($this->generator->objectProps[ApiInterface::RAML_RELATIONSHIPS][ApiInterface::RAML_TYPE])
            ? [] : $this->generator->objectProps[ApiInterface::RAML_RELATIONSHIPS][ApiInterface::RAML_TYPE];
        $this->constructRelations($relTypes);
        $this->setAfterMethods();
    }

    /**
     *  Creates an ApiRequestToken Requests class
     */
    public function createAccessToken(): void
    {
        if (empty($this->generator->types[CustomsInterface::CUSTOM_TYPES_QUERY_PARAMS][ApiInterface::RAML_PROPS]
            [JSONApiInterface::PARAM_ACCESS_TOKEN][ApiInterface::RAML_KEY_DEFAULT]) === false
        ) {
            $this->setAccessTokenContent();
            $fileForm = FileManager::getModulePath($this->generator, true) . $this->generator->formRequestDir
                . PhpInterface::SLASH . JSONApiInterface::CLASS_API_ACCESS_TOKEN
                . PhpInterface::PHP_EXT;

            $isCreated = FileManager::createFile(
                $fileForm, $this->sourceCode,
                FileManager::isRegenerated($this->generator->options)
            );

            if ($isCreated) {
                Console::out($fileForm . PhpInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
            }
        }
    }

    /**
     *  Sets content for ApiRequestToken Requests class
     */
    private function setAccessTokenContent(): void
    {
        $this->setTag();
        $this->sourceCode .= PhpInterface::PHP_NAMESPACE . PhpInterface::SPACE .
            DirsInterface::APPLICATION_DIR . PhpInterface::BACKSLASH . $this->generator->httpDir .
            PhpInterface::BACKSLASH . $this->generator->formRequestDir
            . PhpInterface::SEMICOLON . PHP_EOL . PHP_EOL;

        $this->setUse(PhpInterface::CLASS_CLOSURE, false, true);
        $this->startClass(JSONApiInterface::CLASS_API_ACCESS_TOKEN);
        $methodOptions = new MethodOptions();
        $methodOptions->setName(FromRequestInterface::METHOD_HANDLE);
        $methodOptions->setParams([
            FromRequestInterface::METHOD_PARAM_REQUEST,
            PhpInterface::CLASS_CLOSURE => FromRequestInterface::METHOD_PARAM_NEXT,
        ]);
        $this->startMethod($methodOptions);
        $this->setHandleMethodContent();
        $this->endMethod();
        $this->endClass();
    }

    /**
     *  Sets content for ApiRequestToken Requests class handle() method
     */
    private function setHandleMethodContent(): void
    {
        $this->setTabs(2);
        $this->sourceCode .= PhpInterface::IF . PhpInterface::SPACE . PhpInterface::OPEN_PARENTHESES
            . PhpInterface::OPEN_PARENTHESES . PhpInterface::PHP_TYPES_STRING . PhpInterface::CLOSE_PARENTHESES
            . PhpInterface::SPACE . PhpInterface::DOLLAR_SIGN . FromRequestInterface::METHOD_PARAM_REQUEST
            . PhpInterface::ARROW . JSONApiInterface::PARAM_ACCESS_TOKEN . PhpInterface::SPACE . PhpInterface::EXCLAMATION
            . PhpInterface::EQUALS . PhpInterface::EQUALS . PhpInterface::SPACE
            . PhpInterface::OPEN_PARENTHESES . PhpInterface::PHP_TYPES_STRING . PhpInterface::CLOSE_PARENTHESES
            . PhpInterface::SPACE . MethodsInterface::CONFIG . PhpInterface::OPEN_PARENTHESES . PhpInterface::QUOTES
            . $this->generator->version . PhpInterface::DOT . ConfigInterface::QUERY_PARAMS . PhpInterface::DOT
            . JSONApiInterface::PARAM_ACCESS_TOKEN . PhpInterface::QUOTES
            . PhpInterface::CLOSE_PARENTHESES . PhpInterface::CLOSE_PARENTHESES . PHP_EOL;
        $this->setTabs(2);
        $this->sourceCode .= PhpInterface::OPEN_BRACE . PHP_EOL;
        // response body
        $this->setTabs(3);
        $this->sourceCode .= MethodsInterface::HEADER . PhpInterface::OPEN_PARENTHESES . PhpInterface::QUOTES
            . HTTPMethodsInterface::HTTP_11 . PhpInterface::SPACE . JSONApiInterface::HTTP_RESPONSE_CODE_ACCESS_FORBIDDEN
            . PhpInterface::SPACE . JSONApiInterface::FORBIDDEN
            . PhpInterface::QUOTES . PhpInterface::CLOSE_PARENTHESES
            . PhpInterface::SEMICOLON . PHP_EOL;
        $this->setTabs(3);
        $this->setEchoString('Access forbidden.');
        $this->setTabs(3);
        $this->sourceCode .= PhpInterface::DIE . PhpInterface::SEMICOLON . PHP_EOL;
        $this->setTabs(2);
        $this->sourceCode .= PhpInterface::CLOSE_BRACE . PHP_EOL . PHP_EOL;
        $this->setMethodReturn(PhpInterface::DOLLAR_SIGN . FromRequestInterface::METHOD_PARAM_NEXT
            . PhpInterface::OPEN_PARENTHESES . PhpInterface::DOLLAR_SIGN . FromRequestInterface::METHOD_PARAM_REQUEST
            . PhpInterface::CLOSE_PARENTHESES);
    }
}