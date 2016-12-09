<?php
namespace rjapi\blocks;

interface DefaultInterface
{
    const CONTENT_TYPE = 'application/vnd.api+json';

    const DEFAULT_POSTFIX      = 'Controller';
    const CONTAINER_POSTFIX    = 'Container';
    const QUERY_SEARCH_POSTFIX = 'QuerySearch';
    const DEFAULT_MODULE       = 'Module';
    const DEFAULT_CONTROLLER   = 'BaseMapperController';
    const DEFAULT_MODEL_IN     = 'BaseResourceFormIn';
    const DEFAULT_MODEL_OUT    = 'BaseResourceFormOut';
    const DEFAULT_MAPPER       = 'BaseActiveDataMapper';

    const MAPPER_PREFIX = 'Mapper';
    const FORM_BASE     = 'Base';
    const FORM_PREFIX   = 'Form';
    const FORM_ACTION   = 'Action';
    const FORM_IN       = 'In';
    const FORM_OUT      = 'Out';
}