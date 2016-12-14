<?php
namespace rjapi;

use rjapi\blocks\CustomsInterface;
use rjapi\blocks\DefaultInterface;
use rjapi\blocks\DirsInterface;
use rjapi\blocks\HTTPMethodsInterface;
use rjapi\blocks\PhpEntitiesInterface;
use rjapi\blocks\RamlInterface;
use Illuminate\Console\Command;
use rjapi\controllers\ControllersTrait;

class RJApiGenerator extends Command implements DefaultInterface, PhpEntitiesInterface, HTTPMethodsInterface,
    RamlInterface, CustomsInterface, DirsInterface
{
    use ControllersTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'raml:generate {ramlFile}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'RAML-JSON-API PHP-code generator (based on RAML-types), with complete support of JSON-API data format';

    private $forms = null;
    private $controllers = null;
    private $moduleObject = null;
    private $mappers = null;
    private $containers = null;
    private $excludedSubtypes = [
        self::CUSTOM_TYPES_ATTRIBUTES,
        self::CUSTOM_TYPES_RELATIONSHIPS,
        self::CUSTOM_TYPES_QUERY_SEARCH,
        self::CUSTOM_TYPES_FILTER,
    ];

    public function handle()
    {
        $ramlFile = $this->argument('ramlFile');
        $this->actionIndex($ramlFile);
    }
}