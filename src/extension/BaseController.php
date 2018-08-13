<?php

namespace rjapi\extension;

use Illuminate\Http\Request;

class BaseController extends ApiController
{

    /**
     * @param Request $request
     * @throws \rjapi\exceptions\AttributesException
     */
    public function create(Request $request)
    {
        parent::create($request);
    }

    /**
     * @param Request $request
     * @param int|string $id
     * @throws \rjapi\exceptions\AttributesException
     */
    public function update(Request $request, $id)
    {
        parent::update($request, $id);
    }

    /**
     * @param Request $request
     * @param int|string $id
     */
    public function delete(Request $request, $id)
    {
        parent::delete($request, $id);
    }
}