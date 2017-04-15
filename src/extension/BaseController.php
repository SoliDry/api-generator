<?php
namespace rjapi\extension;

use Illuminate\Routing\Controller;

class BaseController extends Controller implements JSONApiInterface
{
    use BaseControllerTrait;

    // JSON API support enabled by default
    protected $jsonApi = true;
}