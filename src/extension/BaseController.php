<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 14.12.16
 * Time: 22:12
 */

namespace rjapi\extension;

use Illuminate\Routing\Controller;

class BaseController extends Controller implements JSONApiInterface
{
    use BaseControllerTrait;

    // JSON API support enabled by default
    protected $jsonApi = true;
}