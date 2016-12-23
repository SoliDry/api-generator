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
    protected $signature = 'raml:generate {ramlFile} --M|migrations : Whether the migrations will be created.';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'RAML-JSON-API PHP-code generator (based on RAML-types), with complete support of JSON-API data format';

    public function handle()
    {
        $ramlFile = $this->argument('ramlFile');
        $opts = $this->options();
        $this->actionIndex($ramlFile, $opts);
    }
}