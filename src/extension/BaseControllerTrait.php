<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 18.12.16
 * Time: 19:11
 */

namespace rjapi\extension;


use Illuminate\Http\Request;
use rjapi\blocks\DefaultInterface;
use rjapi\blocks\DirsInterface;
use rjapi\blocks\PhpEntitiesInterface;
use rjapi\helpers\Classes;

trait BaseControllerTrait
{
    private $props = [];

    private $methods = [
        self::URI_METHOD_INDEX => self::HTTP_METHOD_GET,
        self::URI_METHOD_VIEW => self::HTTP_METHOD_GET,
        self::URI_METHOD_CREATE => self::HTTP_METHOD_POST,
        self::URI_METHOD_UPDATE => self::HTTP_METHOD_PATCH,
        self::URI_METHOD_DELETE => self::HTTP_METHOD_DELETE,
    ];

    public function __construct()
    {
        $entity = DirsInterface::MODULES_DIR . PhpEntitiesInterface::BACKSLASH . config('v2.name') . PhpEntitiesInterface::BACKSLASH . DirsInterface::HTTP_DIR . PhpEntitiesInterface::BACKSLASH . DirsInterface::MIDDLEWARE_DIR . PhpEntitiesInterface::BACKSLASH . Classes::cutName(Classes::getObjectName($this), DefaultInterface::CONTROLLER_POSTFIX) . DefaultInterface::MIDDLEWARE_POSTFIX;
        $middleware = new $entity();
        $this->props = get_object_vars($middleware);
    }

    /**
     * Output all entries for this Entity
     */
    public function index()
    {

    }

    /**
     * Output one entry determined by unique id as uri param
     * @param int $id
     */
    public function view(int $id)
    {

    }

    /**
     * Creates one entry specified by all input fields in $request
     * @param Request $request
     */
    public function create(Request $request)
    {

    }

    /**
     * Updates one entry determined by unique id as uri param for specified fields in $request
     * @param Request $request
     * @param int $id
     */
    public function update(Request $request, int $id)
    {

    }

    /**
     * Deletes one entry determined by unique id as uri param
     * @param int $id
     */
    public function delete(int $id)
    {

    }
}