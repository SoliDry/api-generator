<?php
namespace rjapi\extension;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use League\Fractal\Resource\Collection;
use rjapi\helpers\ConfigOptions;
use rjapi\blocks\EntitiesTrait;
use rjapi\types\JwtInterface;
use rjapi\types\ModelsInterface;
use rjapi\helpers\Json;
use rjapi\types\PhpInterface;

class ApiController extends Controller
{
    use BaseRelationsTrait,
        OptionsTrait,
        EntitiesTrait,
        JWTTrait,
        FsmTrait,
        SpellCheckTrait,
        BitMaskTrait;

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
    private $defaultOrderBy = [];
    /** @var ConfigOptions configOptions */
    private $configOptions = null;
    /** @var CustomSql customSql */
    private $customSql = null;
    /** @var BitMask bitMask */
    private $bitMask   = null;

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
        if(true === $this->configOptions->isBitMask()) {
            $this->setFlagsIndex($items);
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
        if(true === $this->configOptions->isBitMask()) {
            $this->setFlagsView($item);
        }
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
        // set bit mask
        if(true === $this->configOptions->isBitMask()) {
            $this->setMaskCreate($jsonApiAttributes);
        }
        $this->model->save();
        // jwt
        if($this->configOptions->getIsJwtAction() === true) {
            $this->createJwtUser(); // !!! model is overridden
        }
        // set bit mask from model -> response
        if(true === $this->configOptions->isBitMask()) {
            $this->model = $this->setFlagsCreate();
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
        // set bit mask
        if(true === $this->configOptions->isBitMask()) {
            $this->setFlagsUpdate($model);
        }
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
        // set bit mask
        if(true === $this->configOptions->isBitMask()) {
            $this->setMaskUpdate($model, $jsonApiAttributes);
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
}