<?php

namespace SoliDry\Extension;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use League\Fractal\Resource\Collection;
use SoliDry\Helpers\ConfigOptions;
use SoliDry\Blocks\EntitiesTrait;
use SoliDry\Helpers\JsonApiResponse;
use SoliDry\Types\HTTPMethodsInterface;
use SoliDry\Types\JwtInterface;
use SoliDry\Types\ModelsInterface;
use SoliDry\Helpers\Json;
use SoliDry\Types\PhpInterface;

class ApiController extends Controller implements JSONApiInterface
{
    use BaseRelationsTrait,
        OptionsTrait,
        EntitiesTrait,
        JWTTrait,
        FsmTrait,
        SpellCheckTrait,
        BitMaskTrait,
        CacheTrait;

    // JSON API support enabled by default
    protected $jsonApi = true;

    protected $props = [];
    protected $entity;
    /** @var BaseModel $model */
    protected $model;
    /** @var EntitiesTrait $modelEntity */
    private   $modelEntity;
    protected $formRequest;
    private   $relsRemoved    = false;
    private   $defaultOrderBy = [];
    /** @var ConfigOptions $configOptions */
    protected $configOptions;
    /** @var CustomSql $customSql */
    protected $customSql;
    /** @var BitMask $bitMask */
    private $bitMask;

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
        if ($this->jsonApi === false && in_array($calledMethod, $this->jsonApiMethods)) {
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
     * Responds with header of an allowed/available http methods
     * @return mixed
     */
    public function options()
    {
        // this seems like needless params passed by default, but they needed for backward compatibility in Laravel prev versions
        return response('', 200)->withHeaders([
            'Allow'                            => HTTPMethodsInterface::HTTP_METHODS_AVAILABLE,
            JSONApiInterface::CONTENT_TYPE_KEY => JSONApiInterface::HEADER_CONTENT_TYPE_VALUE,
        ]);
    }

    /**
     * GET Output all entries for this Entity with page/limit pagination support
     *
     * @param Request $request
     * @return Response
     * @throws \SoliDry\Exceptions\AttributesException
     */
    public function index(Request $request) : Response
    {
        $meta       = [];
        $sqlOptions = $this->setSqlOptions($request);
        if (true === $this->isTree) {
            $tree = $this->getAllTreeEntities($sqlOptions);
            $meta = [strtolower($this->entity) . PhpInterface::UNDERSCORE . JSONApiInterface::META_TREE => $tree->toArray()];
        }

        $this->setPagination(true);
        /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $pages */
        if ($this->configOptions->isCached()) {
            $pages = $this->getCached($request, $sqlOptions);
        } else {
            $pages = $this->getEntities($sqlOptions);
        }

        if ($this->configOptions->isBitMask() === true) {
            $this->setFlagsIndex($pages);
        }

        // set meta info on page, limit, count
        //$this->setMetaPageInfo($meta, $sqlOptions, \count($items));

        $resource = Json::getResource($this->formRequest, $pages, $this->entity, true, $meta);
        return $this->getResponse(Json::prepareSerializedData($resource, $sqlOptions->getData()));
    }

    /**
     * GET Output one entry determined by unique id as uri param
     *
     * @param Request $request
     * @param int|string $id
     * @return Response
     * @throws \SoliDry\Exceptions\AttributesException
     */
    public function view(Request $request, $id) : Response
    {
        $meta       = [];
        $data       = ($request->input(ModelsInterface::PARAM_DATA) === null) ? ModelsInterface::DEFAULT_DATA
            : Json::decode(urldecode($request->input(ModelsInterface::PARAM_DATA)));
        $sqlOptions = $this->setSqlOptions($request);
        $sqlOptions->setId($id);
        $sqlOptions->setData($data);

        if (true === $this->isTree) {
            $tree = $this->getSubTreeEntities($sqlOptions, $id);
            $meta = [strtolower($this->entity) . PhpInterface::UNDERSCORE . JSONApiInterface::META_TREE => $tree];
        }

        if ($this->configOptions->isCached()) {
            $item = $this->getCached($request, $sqlOptions);
        } else {
            $item = $this->getEntity($id, $data);
        }

        if (true === $this->configOptions->isBitMask()) {
            $this->setFlagsView($item);
        }

        $resource = Json::getResource($this->formRequest, $item, $this->entity, false, $meta);
        return $this->getResponse(Json::prepareSerializedData($resource,  $data));
    }

    /**
     * POST Creates one entry specified by all input fields in $request
     *
     * @param Request $request
     * @return Response
     * @throws \SoliDry\Exceptions\AttributesException
     */
    public function create(Request $request) : Response
    {
        $meta              = [];
        $json              = Json::decode($request->getContent());
        $jsonApiAttributes = Json::getAttributes($json);

        // FSM initial state check
        if ($this->configOptions->isStateMachine() === true) {
            $this->checkFsmCreate($jsonApiAttributes);
        }

        // spell check
        if ($this->configOptions->isSpellCheck() === true) {
            $meta = $this->spellCheck($jsonApiAttributes);
        }

        // fill in model
        foreach ($this->props as $k => $v) {
            // request fields should match FormRequest fields
            if (isset($jsonApiAttributes[$k])) {
                $this->model->$k = $jsonApiAttributes[$k];
            }
        }

        // set bit mask
        if (true === $this->configOptions->isBitMask()) {
            $this->setMaskCreate($jsonApiAttributes);
        }
        $this->model->save();

        // jwt
        if ($this->configOptions->getIsJwtAction() === true) {
            $this->createJwtUser(); // !!! model is overridden
        }

        // set bit mask from model -> response
        if (true === $this->configOptions->isBitMask()) {
            $this->model = $this->setFlagsCreate();
        }

        $this->setRelationships($json, $this->model->id);
        $resource = Json::getResource($this->formRequest, $this->model, $this->entity, false, $meta);
        return $this->getResponse(Json::prepareSerializedData($resource), JSONApiInterface::HTTP_RESPONSE_CODE_CREATED);
    }

    /**
     * PATCH Updates one entry determined by unique id as uri param for specified fields in $request
     *
     * @param Request $request
     * @param int|string $id
     * @return Response
     * @throws \SoliDry\Exceptions\AttributesException
     */
    public function update(Request $request, $id) : Response
    {
        $meta = [];

        // get json raw input and parse attrs
        $json              = Json::decode($request->getContent());
        $jsonApiAttributes = Json::getAttributes($json);
        $model             = $this->getEntity($id);

        // FSM transition check
        if ($this->configOptions->isStateMachine() === true) {
            $this->checkFsmUpdate($jsonApiAttributes, $model);
        }

        // spell check
        if ($this->configOptions->isSpellCheck() === true) {
            $meta = $this->spellCheck($jsonApiAttributes);
        }

        $this->processUpdate($model, $jsonApiAttributes);
        $model->save();

        $this->setRelationships($json, $model->id, true);

        // set bit mask
        if (true === $this->configOptions->isBitMask()) {
            $this->setFlagsUpdate($model);
        }

        $resource = Json::getResource($this->formRequest, $model, $this->entity, false, $meta);
        return $this->getResponse(Json::prepareSerializedData($resource));
    }

    /**
     * Process model update
     * @param $model
     * @param array $jsonApiAttributes
     * @throws \SoliDry\Exceptions\AttributesException
     */
    private function processUpdate($model, array $jsonApiAttributes)
    {
        // jwt
        $isJwtAction = $this->configOptions->getIsJwtAction();
        if ($isJwtAction === true && (bool)$jsonApiAttributes[JwtInterface::JWT] === true) {
            $this->updateJwtUser($model, $jsonApiAttributes);
        } else { // standard processing
            foreach ($this->props as $k => $v) {
                // request fields should match FormRequest fields
                if (empty($jsonApiAttributes[$k]) === false) {
                    if ($isJwtAction === true && $k === JwtInterface::PASSWORD) {// it is a regular query with password updated and jwt enabled - hash the password
                        $model->$k = password_hash($jsonApiAttributes[$k], PASSWORD_DEFAULT);
                    } else {
                        $model->$k = $jsonApiAttributes[$k];
                    }
                }
            }
        }

        // set bit mask
        if (true === $this->configOptions->isBitMask()) {
            $this->setMaskUpdate($model, $jsonApiAttributes);
        }
    }

    /**
     * DELETE Deletes one entry determined by unique id as uri param
     *
     * @param Request $request
     * @param int|string $id
     * @return Response
     */
    public function delete(Request $request, $id) : Response
    {
        $model = $this->getEntity($id);
        if ($model !== null) {
            $model->delete();
        }

        return $this->getResponse(Json::prepareSerializedData(new Collection()), JSONApiInterface::HTTP_RESPONSE_CODE_NO_CONTENT);
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
     * Prepares Response object to return with particular http response, headers and body
     *
     * @param string $json
     * @param int $responseCode
     * @return Response
     */
    public function getResponse(string $json, int $responseCode = JSONApiInterface::HTTP_RESPONSE_CODE_OK) : Response
    {
        return (new JsonApiResponse())->getResponse($json, $responseCode);
    }
}