<?php
namespace rjapi\extension;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use League\Fractal\Resource\Collection;
use rjapi\helpers\ConfigOptions;
use rjapi\types\ConfigInterface;
use rjapi\blocks\EntitiesTrait;
use rjapi\types\JwtInterface;
use rjapi\types\ModelsInterface;
use rjapi\types\RamlInterface;
use rjapi\helpers\ConfigHelper;
use rjapi\helpers\Json;
use rjapi\helpers\MigrationsHelper;
use rjapi\helpers\SqlOptions;
use rjapi\types\PhpInterface;

class ApiController extends Controller
{
    use BaseRelationsTrait,
        EntitiesTrait,
        JWTTrait,
        FsmTrait,
        SpellCheckTrait;

    // JSON API support enabled by default
    protected $jsonApi = true;

    private $props  = [];
    private $entity = null;
    /** @var BaseModel $model */
    private $model = null;
    /** @var EntitiesTrait $modelEntity */
    private $modelEntity = null;
    private $middleWare  = null;
    private $relsRemoved = false;
    // default query params value
    private $defaultPage    = 0;
    private $defaultLimit   = 0;
    private $defaultSort    = '';
    private $isTree         = false;
    private $defaultOrderBy = [];
    /** @var ConfigOptions configOptions */
    private $configOptions = null;
    /** @var CustomSql customSql */
    private $customSql = null;

    private $jsonApiMethods = [
        JSONApiInterface::URI_METHOD_INDEX,
        JSONApiInterface::URI_METHOD_VIEW,
        JSONApiInterface::URI_METHOD_CREATE,
        JSONApiInterface::URI_METHOD_UPDATE,
        JSONApiInterface::URI_METHOD_DELETE,
        JSONApiInterface::URI_METHOD_RELATIONS,
    ];

    /**
     * BaseControllerTrait constructor.
     *
     * @param Route $route
     */
    public function __construct(Route $route)
    {
        // add relations to json api methods array
        $this->addRelationMethods();
        $actionName   = $route->getActionName();
        $calledMethod = substr($actionName, strpos($actionName, PhpInterface::AT) + 1);
        if($this->jsonApi === false && in_array($calledMethod, $this->jsonApiMethods)) {
            Json::outputErrors(
                [
                    [
                        JSONApiInterface::ERROR_TITLE  => 'JSON API support disabled',
                        JSONApiInterface::ERROR_DETAIL => 'JSON API method ' . $calledMethod
                            .
                            ' was called. You can`t call this method while JSON API support is disabled.',
                    ],
                ]
            );
        }
        $this->setEntities();
        $this->setDefaults();
        $this->setConfigOptions($calledMethod);
    }

    /**
     * GET Output all entries for this Entity with page/limit pagination support
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        $meta       = [];
        $sqlOptions = $this->setSqlOptions($request);
        if(true === $this->isTree) {
            $tree = $this->getAllTreeEntities($sqlOptions);
            $meta = [strtolower($this->entity) . PhpInterface::UNDERSCORE . JSONApiInterface::META_TREE => $tree->toArray()];
        }
        if($this->customSql->isEnabled()) {
            $items = $this->getCustomSqlEntities($this->customSql);
        }
        else {
            $items = $this->getAllEntities($sqlOptions);
        }
        $resource = Json::getResource($this->middleWare, $items, $this->entity, true, $meta);
        Json::outputSerializedData($resource, JSONApiInterface::HTTP_RESPONSE_CODE_OK, $sqlOptions->getData());
    }

    /**
     * GET Output one entry determined by unique id as uri param
     *
     * @param Request $request
     * @param int $id
     */
    public function view(Request $request, int $id)
    {
        $meta = [];
        $data = ($request->input(ModelsInterface::PARAM_DATA) === null) ? ModelsInterface::DEFAULT_DATA
            : json_decode(urldecode($request->input(ModelsInterface::PARAM_DATA)), true);
        if(true === $this->isTree) {
            $sqlOptions = $this->setSqlOptions($request);
            $tree       = $this->getSubTreeEntities($sqlOptions, $id);
            $meta       = [strtolower($this->entity) . PhpInterface::UNDERSCORE . JSONApiInterface::META_TREE => $tree];
        }
        $item     = $this->getEntity($id, $data);
        $resource = Json::getResource($this->middleWare, $item, $this->entity, false, $meta);
        Json::outputSerializedData($resource, JSONApiInterface::HTTP_RESPONSE_CODE_OK, $data);
    }

    /**
     * POST Creates one entry specified by all input fields in $request
     *
     * @param Request $request
     */
    public function create(Request $request)
    {
        $meta              = [];
        $json              = Json::decode($request->getContent());
        $jsonApiAttributes = Json::getAttributes($json);
        // FSM initial state check
        if($this->configOptions->isStateMachine() === true) {
            $this->checkFsmCreate($jsonApiAttributes);
        }
        // spell check
        if($this->configOptions->isSpellCheck() === true) {
            $meta = $this->spellCheck($jsonApiAttributes);
        }
        // fill in model
        foreach($this->props as $k => $v) {
            // request fields should match Middleware fields
            if(isset($jsonApiAttributes[$k])) {
                $this->model->$k = $jsonApiAttributes[$k];
            }
        }
        $this->model->save();
        // jwt
        if($this->configOptions->getIsJwtAction() === true) {
            $this->createJwtUser();
        }
        $this->setRelationships($json, $this->model->id);
        $resource = Json::getResource($this->middleWare, $this->model, $this->entity, false, $meta);
        Json::outputSerializedData($resource, JSONApiInterface::HTTP_RESPONSE_CODE_CREATED);
    }

    /**
     * PATCH Updates one entry determined by unique id as uri param for specified fields in $request
     *
     * @param Request $request
     * @param int $id
     */
    public function update(Request $request, int $id)
    {
        $meta = [];
        // get json raw input and parse attrs
        $json              = Json::decode($request->getContent());
        $jsonApiAttributes = Json::getAttributes($json);
        $model             = $this->getEntity($id);
        // FSM transition check
        if($this->configOptions->isStateMachine() === true) {
            $this->checkFsmUpdate($jsonApiAttributes, $model);
        }
        // spell check
        if($this->configOptions->isSpellCheck() === true) {
            $meta = $this->spellCheck($jsonApiAttributes);
        }
        $this->processUpdate($model, $jsonApiAttributes);
        $model->save();
        $this->setRelationships($json, $model->id, true);
        $resource = Json::getResource($this->middleWare, $model, $this->entity, false, $meta);
        Json::outputSerializedData($resource);
    }

    /**
     * Process model update
     * @param $model
     * @param array $jsonApiAttributes
     */
    private function processUpdate($model, array $jsonApiAttributes)
    {
        // jwt
        $isJwtAction = $this->configOptions->getIsJwtAction();
        if($isJwtAction === true && (bool)$jsonApiAttributes[JwtInterface::JWT] === true) {
            $this->updateJwtUser($model, $jsonApiAttributes);
        }
        else { // standard processing
            foreach($this->props as $k => $v) {
                // request fields should match Middleware fields
                if(empty($jsonApiAttributes[$k]) === false) {
                    if($isJwtAction === true && $k === JwtInterface::PASSWORD) {// it is a regular query with password updated and jwt enabled - hash the password
                        $model->$k = password_hash($jsonApiAttributes[$k], PASSWORD_DEFAULT);
                    }
                    else {
                        $model->$k = $jsonApiAttributes[$k];
                    }
                }
            }
        }
    }

    /**
     * DELETE Deletes one entry determined by unique id as uri param
     *
     * @param int $id
     */
    public function delete(int $id)
    {
        $model = $this->getEntity($id);
        if($model !== null) {
            $model->delete();
        }
        Json::outputSerializedData(new Collection(), JSONApiInterface::HTTP_RESPONSE_CODE_NO_CONTENT);
    }

    /**
     *  Adds {HTTPMethod}Relations to array of route methods
     */
    private function addRelationMethods()
    {
        $ucRelations            = ucfirst(JSONApiInterface::URI_METHOD_RELATIONS);
        $this->jsonApiMethods[] = JSONApiInterface::URI_METHOD_CREATE . $ucRelations;
        $this->jsonApiMethods[] = JSONApiInterface::URI_METHOD_UPDATE . $ucRelations;
        $this->jsonApiMethods[] = JSONApiInterface::URI_METHOD_DELETE . $ucRelations;
    }

    /**
     *  Sets the default config based parameters
     */
    private function setDefaults()
    {
        $this->defaultPage  = ConfigHelper::getQueryParam(ModelsInterface::PARAM_PAGE);
        $this->defaultLimit = ConfigHelper::getQueryParam(ModelsInterface::PARAM_LIMIT);
        $this->defaultSort  = ConfigHelper::getQueryParam(ModelsInterface::PARAM_SORT);
        $this->isTree       = ConfigHelper::getNestedParam(ConfigInterface::TREES, $this->entity, true);
    }

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
     *
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