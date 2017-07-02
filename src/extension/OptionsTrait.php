<?php
namespace rjapi\extension;

use Illuminate\Http\Request;
use rjapi\helpers\ConfigHelper;
use rjapi\helpers\ConfigOptions;
use rjapi\helpers\Json;
use rjapi\helpers\MigrationsHelper;
use rjapi\helpers\SqlOptions;
use rjapi\types\ConfigInterface;
use rjapi\types\ModelsInterface;
use rjapi\types\RamlInterface;

trait OptionsTrait
{
    /**
     * Sets SqlOptions params
     * @param Request $request
     * @return SqlOptions
     */
    private function setSqlOptions(Request $request)
    {
        $sqlOptions = new SqlOptions();
        $page       = ($request->input(ModelsInterface::PARAM_PAGE) === null) ? $this->defaultPage :
            $request->input(ModelsInterface::PARAM_PAGE);
        $limit      = ($request->input(ModelsInterface::PARAM_LIMIT) === null) ? $this->defaultLimit :
            $request->input(ModelsInterface::PARAM_LIMIT);
        $sort       = ($request->input(ModelsInterface::PARAM_SORT) === null) ? $this->defaultSort :
            $request->input(ModelsInterface::PARAM_SORT);
        $data       = ($request->input(ModelsInterface::PARAM_DATA) === null) ? ModelsInterface::DEFAULT_DATA
            : Json::decode($request->input(ModelsInterface::PARAM_DATA));
        $orderBy    = ($request->input(ModelsInterface::PARAM_ORDER_BY) === null) ? [RamlInterface::RAML_ID => $sort]
            : Json::decode($request->input(ModelsInterface::PARAM_ORDER_BY));
        $filter     = ($request->input(ModelsInterface::PARAM_FILTER) === null) ? [] : Json::decode($request->input(ModelsInterface::PARAM_FILTER));
        $sqlOptions->setLimit($limit);
        $sqlOptions->setPage($page);
        $sqlOptions->setData($data);
        $sqlOptions->setOrderBy($orderBy);
        $sqlOptions->setFilter($filter);

        return $sqlOptions;
    }

    /**
     * Sets options based on config.php settings
     * @param string $calledMethod
     */
    private function setConfigOptions(string $calledMethod)
    {
        $this->configOptions = new ConfigOptions();
        $this->configOptions->setJwtIsEnabled(ConfigHelper::getNestedParam(ConfigInterface::JWT, ConfigInterface::ENABLED));
        $this->configOptions->setJwtTable(ConfigHelper::getNestedParam(ConfigInterface::JWT, ModelsInterface::MIGRATION_TABLE));
        if($this->configOptions->getJwtIsEnabled() === true && $this->configOptions->getJwtTable() === MigrationsHelper::getTableName($this->entity)) {// if jwt enabled=true and tables are equal
            $this->configOptions->setIsJwtAction(true);
        }
        if($calledMethod !== JSONApiInterface::URI_METHOD_DELETE) {
            $bitMaskParams = ConfigHelper::getNestedParam(ConfigInterface::BIT_MASK, MigrationsHelper::getTableName($this->entity));
            if ($bitMaskParams !== null) {
                $this->configOptions->setBitMask(true);
                $this->bitMask = new BitMask($this->entity, $bitMaskParams);
            }
        }
        // set those only for create/update
        if(in_array($calledMethod, [JSONApiInterface::URI_METHOD_CREATE, JSONApiInterface::URI_METHOD_UPDATE]) === true) {
            // state machine for concrete entity == table
            $stateMachine = ConfigHelper::getNestedParam(ConfigInterface::STATE_MACHINE, MigrationsHelper::getTableName($this->entity));
            if($stateMachine !== null) {
                $this->configOptions->setStateMachine(true);
            }
            // spell check if enabled
            $spellCheck = ConfigHelper::getNestedParam(ConfigInterface::SPELL_CHECK, MigrationsHelper::getTableName($this->entity));
            if($spellCheck !== null) {
                $this->configOptions->setSpellCheck(true);
            }
        }
        // set those only for index
        if($calledMethod === JSONApiInterface::URI_METHOD_INDEX) {
            $this->customSql = new CustomSql($this->entity);
        }
    }
}