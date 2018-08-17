<?php

namespace rjapi\extension;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use rjapi\exceptions\HeadersException;
use rjapi\helpers\Json;
use rjapi\helpers\Request as RequestHelper;

class BaseController extends ApiController
{

    /**
     * BaseController constructor.
     * @param Route $route
     * @throws HeadersException
     */
    public function __construct(Route $route)
    {
        parent::__construct($route);
        if (in_array($route->getActionMethod(), self::AVAILABLE_BULKS, true) && RequestHelper::isExt(request(), self::EXT_BULK) === false) {
            throw new HeadersException('There is no ' . self::EXT_BULK . ' value in ' . self::EXT . ' key of ' . self::CONTENT_TYPE_KEY . ' header');
        }
    }

    /**
     * Creates bulk of items in transaction mode
     *
     * @param Request $request
     * @throws \LogicException
     * @throws \rjapi\exceptions\AttributesException
     */
    public function createBulk(Request $request)
    {
        $meta              = [];
        $json              = Json::decode($request->getContent());
        $jsonApiAttributes = Json::getBulkAttributes($json);
        $collection = new Collection();

        try {
            DB::beginTransaction();
            foreach ($jsonApiAttributes as $jsonObject) {
                // FSM initial state check
                if ($this->configOptions->isStateMachine() === true) {
                    $this->checkFsmCreate($jsonObject);
                }

                // spell check
                if ($this->configOptions->isSpellCheck() === true) {
                    $meta[] = $this->spellCheck($jsonObject);
                }

                // fill in model
                foreach ($this->props as $k => $v) {
                    // request fields should match Middleware fields
                    if (isset($jsonObject[$k])) {
                        $this->model->$k = $jsonObject[$k];
                    }
                }

                // set bit mask
                if (true === $this->configOptions->isBitMask()) {
                    $this->setMaskCreate($jsonObject);
                }

                $collection->push($this->model);
                $this->model->save();

                // jwt
                if ($this->configOptions->getIsJwtAction() === true) {
                    $this->createJwtUser(); // !!! model is overridden
                }

                // set bit mask from model -> response
                if (true === $this->configOptions->isBitMask()) {
                    $this->model = $this->setFlagsCreate();
                }
            }
            DB::commit();
        } catch (\PDOException $e) {
            echo $e->getTraceAsString();
            DB::rollBack();
        }

        $resource = Json::getResource($this->middleWare, $collection, $this->entity, true, $meta);
        Json::outputSerializedData($resource, JSONApiInterface::HTTP_RESPONSE_CODE_CREATED);
    }

    /**
     * @param Request $request
     * @throws HeadersException
     * @throws \rjapi\exceptions\AttributesException
     */
    public function updateBulk(Request $request)
    {

    }

    /**
     * @param Request $request
     * @throws HeadersException
     */
    public function deleteBulk(Request $request)
    {

    }
}