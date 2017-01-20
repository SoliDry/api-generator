<?php
namespace rjapi\blocks;

use rjapi\controllers\ControllersTrait;
use rjapi\extension\JSONApiInterface;
use rjapi\helpers\Classes;
use rjapi\helpers\Console;

class Config implements ConfigInterface
{
    use ContentManager, ConfigTrait;

    protected $sourceCode = '';
    /** @var ControllersTrait generator */
    protected $generator = null;
    protected $className = null;

    public function __construct($generator)
    {
        $this->generator = $generator;
        $this->className = Classes::getClassName($this->generator->objectName);
    }

    public function setCodeState($generator)
    {
        $this->generator = $generator;
    }

    public function create()
    {
        if(empty($this->generator->types[CustomsInterface::CUSTOM_TYPES_QUERY_PARAMS]) === false)
        {
            $queryParams = $this->generator->types[CustomsInterface::CUSTOM_TYPES_QUERY_PARAMS][RamlInterface::RAML_PROPS];
            $this->setContent($queryParams);
            // create config file
            $file = $this->generator->formatConfigPath() .
                ModulesInterface::CONFIG_FILENAME . PhpEntitiesInterface::PHP_EXT;
            $isCreated = FileManager::createFile($file, $this->sourceCode, true);
            if ($isCreated)
            {
                Console::out($file . PhpEntitiesInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
            }
        }
    }

    /**
     * @param string $name  Version name aka: V1, V2 etc
     */
    private function setName(string $name)
    {
        $this->sourceCode .= PhpEntitiesInterface::TAB_PSR4 .
            PhpEntitiesInterface::QUOTES . ModulesInterface::KEY_NAME
            . PhpEntitiesInterface::QUOTES . PhpEntitiesInterface::DOUBLE_ARROW . PhpEntitiesInterface::QUOTES .
            ucfirst($name) . PhpEntitiesInterface::QUOTES . PhpEntitiesInterface::COMMA . PHP_EOL;
    }

    /**
     * @param int $limit
     */
    private function setLimit(int $limit)
    {
        $this->setTabs(2);
        $this->sourceCode .= PhpEntitiesInterface::QUOTES . ModelsInterface::PARAM_LIMIT . PhpEntitiesInterface::QUOTES
            . PhpEntitiesInterface::SPACE . PhpEntitiesInterface::DOUBLE_ARROW .
            PhpEntitiesInterface::SPACE . $limit . PhpEntitiesInterface::COMMA . PHP_EOL;
    }

    /**
     * @param string $sort
     */
    private function setSort(string $sort)
    {
        $this->setTabs(2);
        $this->sourceCode .= PhpEntitiesInterface::QUOTES . ModelsInterface::PARAM_SORT . PhpEntitiesInterface::QUOTES
            . PhpEntitiesInterface::SPACE . PhpEntitiesInterface::DOUBLE_ARROW .
            PhpEntitiesInterface::SPACE . PhpEntitiesInterface::QUOTES . $sort
            . PhpEntitiesInterface::QUOTES . PhpEntitiesInterface::COMMA . PHP_EOL;
    }

    /**
     * @param int $page
     */
    private function setPage(int $page)
    {
        $this->setTabs(2);
        $this->sourceCode .= PhpEntitiesInterface::QUOTES . ModelsInterface::PARAM_PAGE . PhpEntitiesInterface::QUOTES
            . PhpEntitiesInterface::SPACE . PhpEntitiesInterface::DOUBLE_ARROW .
            PhpEntitiesInterface::SPACE . $page . PhpEntitiesInterface::COMMA . PHP_EOL;
    }

    private function setAccessToken(string $token)
    {
        $this->setTabs(2);
        $this->sourceCode .= PhpEntitiesInterface::QUOTES . JSONApiInterface::PARAM_ACCESS_TOKEN . PhpEntitiesInterface::QUOTES
            . PhpEntitiesInterface::SPACE . PhpEntitiesInterface::DOUBLE_ARROW .
            PhpEntitiesInterface::SPACE . PhpEntitiesInterface::QUOTES . $token . PhpEntitiesInterface::QUOTES . PhpEntitiesInterface::COMMA . PHP_EOL;
    }

    private function setContent(array $queryParams)
    {
        $this->setTag();
        $this->openRoot();
        $this->setName($this->generator->version);
        $this->openParams();
        if(empty($queryParams[ModelsInterface::PARAM_LIMIT][RamlInterface::RAML_KEY_DEFAULT]) === false)
        {
            $this->setLimit($queryParams[ModelsInterface::PARAM_LIMIT][RamlInterface::RAML_KEY_DEFAULT]);
        }
        if(empty($queryParams[ModelsInterface::PARAM_SORT][RamlInterface::RAML_KEY_DEFAULT]) === false)
        {
            $this->setSort($queryParams[ModelsInterface::PARAM_SORT][RamlInterface::RAML_KEY_DEFAULT]);
        }
        if(empty($queryParams[ModelsInterface::PARAM_PAGE][RamlInterface::RAML_KEY_DEFAULT]) === false)
        {
            $this->setPage($queryParams[ModelsInterface::PARAM_PAGE][RamlInterface::RAML_KEY_DEFAULT]);
        }
        if(empty($queryParams[JSONApiInterface::PARAM_ACCESS_TOKEN][RamlInterface::RAML_KEY_DEFAULT]) === false)
        {
            $this->setAccessToken($queryParams[JSONApiInterface::PARAM_ACCESS_TOKEN][RamlInterface::RAML_KEY_DEFAULT]);
        }
        $this->closeParams();
        $this->closeRoot();
    }
}