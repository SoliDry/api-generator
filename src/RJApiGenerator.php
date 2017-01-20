<?php
namespace rjapi;

use rjapi\types\CustomsInterface;
use rjapi\types\DefaultInterface;
use rjapi\types\DirsInterface;
use rjapi\types\PhpInterface;
use rjapi\types\RamlInterface;
use Illuminate\Console\Command;
use rjapi\controllers\ControllersTrait;

class RJApiGenerator extends Command implements DefaultInterface, PhpInterface, RamlInterface,
    CustomsInterface, DirsInterface
{
    use ControllersTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'raml:generate {ramlFile} {--migrations} {--regenerate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'RAML-JSON-API PHP-code generator (based on RAML-types), with complete support of JSON-API data format';

    /**
     *  Laravel handler for console commands
     */
    public function handle()
    {
        $ramlFile = $this->argument('ramlFile');
        $this->actionIndex($ramlFile);
    }
}