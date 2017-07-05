<?php
namespace rjapi;

use rjapi\controllers\BaseCommand;

class RJApiGenerator extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'raml:generate {ramlFile} {--migrations} {--regenerate} {--merge=} {--no-history}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'RAML-JSON-API PHP-code generator (based on RAML-types) for Laravel, with complete support of JSON-API data format';

    /**
     *  Laravel handler for console commands
     */
    public function handle()
    {
        $ramlFile = $this->argument('ramlFile');
        $this->actionIndex($ramlFile);
    }
}