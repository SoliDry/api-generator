<?php

namespace SoliDry\Blocks;

use SoliDry\ApiGenerator;
use SoliDry\Controllers\BaseCommand;
use SoliDry\Helpers\Classes;
use SoliDry\Types\ApiInterface;
use SoliDry\Types\DefaultInterface;
use SoliDry\Types\DocumentationInterface;
use SoliDry\Types\MethodsInterface;
use SoliDry\Types\PhpInterface;

/**
 * Class Documentation
 *
 * @package SoliDry\Blocks
 *
 * @property BaseCommand generator
 */
abstract class Documentation
{

    use ContentManager;

    protected $generator;
    protected $sourceCode = '';
    protected $className;

    /**
     * Controllers constructor.
     *
     * @param ApiGenerator $generator
     */
    public function __construct($generator)
    {
        $this->generator = $generator;
        $this->className = Classes::getClassName($this->generator->objectName);
    }

    protected function setDefaultDocs()
    {
        $this->setComment(DefaultInterface::METHOD_START);

        $this->openComment();

        // generate basic info
        $this->setStarredComment(DocumentationInterface::OA_INFO . PhpInterface::OPEN_PARENTHESES);

        if (empty($this->generator->data[ApiInterface::API_INFO][ApiInterface::API_TITLE]) === false) {
            $this->setStarredComment('title="' . $this->generator->data[ApiInterface::API_INFO][ApiInterface::API_TITLE] . '",',
                1, 1);
        }

        if (empty($this->generator->data[ApiInterface::API_INFO][ApiInterface::API_VERSION]) === false) {
            $this->setStarredComment('version="' . $this->generator->data[ApiInterface::API_INFO][ApiInterface::API_VERSION] . '",',
                1, 1);
        }

        if (empty($this->generator->data[ApiInterface::API_INFO][ApiInterface::API_DESCRIPTION]) === false) {
            $this->setStarredComment('description="' . $this->generator->data[ApiInterface::API_INFO][ApiInterface::API_DESCRIPTION] . '",',
                1, 1);
        }

        // generate contact info
        $this->setStarredComment(DocumentationInterface::OA_CONTACT . PhpInterface::OPEN_PARENTHESES,
            1, 1);

        if (empty($this->generator->data[ApiInterface::API_INFO][ApiInterface::API_CONTACT][ApiInterface::API_EMAIL]) === false) {
            $this->setStarredComment('email="' . $this->generator->data[ApiInterface::API_INFO][ApiInterface::API_CONTACT][ApiInterface::API_EMAIL] . '",',
                1, 2);
        }

        if (empty($this->generator->data[ApiInterface::API_INFO][ApiInterface::API_CONTACT][ApiInterface::API_NAME]) === false) {
            $this->setStarredComment('name="' . $this->generator->data[ApiInterface::API_INFO][ApiInterface::API_CONTACT][ApiInterface::API_NAME] . '",',
                1, 2);
        }

        if (empty($this->generator->data[ApiInterface::API_INFO][ApiInterface::API_CONTACT][ApiInterface::API_URL]) === false) {
            $this->setStarredComment('url="' . $this->generator->data[ApiInterface::API_INFO][ApiInterface::API_CONTACT][ApiInterface::API_URL] . '",',
                1, 2);
        }

        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES, 1, 1);

        // generate license info
        $this->setStarredComment(DocumentationInterface::OA_LICENSE . PhpInterface::OPEN_PARENTHESES,
            1, 1);

        if (empty($this->generator->data[ApiInterface::API_INFO][ApiInterface::API_LICENSE][ApiInterface::API_NAME]) === false) {
            $this->setStarredComment('name="' . $this->generator->data[ApiInterface::API_INFO][ApiInterface::API_LICENSE][ApiInterface::API_NAME] . '",',
                1, 2);
        }

        if (empty($this->generator->data[ApiInterface::API_INFO][ApiInterface::API_LICENSE][ApiInterface::API_URL]) === false) {
            $this->setStarredComment('url="' . $this->generator->data[ApiInterface::API_INFO][ApiInterface::API_LICENSE][ApiInterface::API_URL] . '",',
                1, 2);
        }

        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES, 1, 1);

        $this->closeComment();

        $this->setComment(DefaultInterface::METHOD_END);
    }

    /**
     *  Sets doc comments for every controller
     */
    protected function setControllersDocs(): void
    {
        $this->setComment(DefaultInterface::METHOD_START);

        $this->setIndex();

        $this->setView();

        $this->setCreate();

        $this->setUpdate();

        $this->setDelete();

        $this->setComment(DefaultInterface::METHOD_END);
    }

    private function setIndex()
    {
        $this->openComment();

        $this->setStarredComment(DocumentationInterface::OA_GET . PhpInterface::OPEN_PARENTHESES);

        $this->setStarredComment('path="' . PhpInterface::SLASH . $this->generator->version . PhpInterface::SLASH
            . strtolower($this->generator->objectName) . '",', 1, 1);

        $this->setStarredComment('summary="Get ' . $this->generator->objectName . ' source for '
            . MethodsInterface::INDEX . '",', 1, 1);

        $this->setStarredComment('tags="{"' . $this->generator->objectName . DefaultInterface::CONTROLLER_POSTFIX
            . '"},', 1, 1);

        // define params
        $this->setStarredComment(DocumentationInterface::OA_PARAMETER . PhpInterface::OPEN_PARENTHESES, 1, 1);
        $this->setStarredComment('in="query",', 1, 2);
        $this->setStarredComment('name="include",', 1, 2);
        $this->setStarredComment('required=false,', 1, 2);
        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES, 1, 1);

        $this->setStarredComment(DocumentationInterface::OA_PARAMETER . PhpInterface::OPEN_PARENTHESES, 1, 1);
        $this->setStarredComment('in="query",', 1, 2);
        $this->setStarredComment('name="page",', 1, 2);
        $this->setStarredComment('required=false,', 1, 2);
        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES, 1, 1);

        $this->setStarredComment(DocumentationInterface::OA_PARAMETER . PhpInterface::OPEN_PARENTHESES, 1, 1);
        $this->setStarredComment('in="query",', 1, 2);
        $this->setStarredComment('name="limit",', 1, 2);
        $this->setStarredComment('required=false,', 1, 2);
        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES, 1, 1);

        $this->setStarredComment(DocumentationInterface::OA_PARAMETER . PhpInterface::OPEN_PARENTHESES, 1, 1);
        $this->setStarredComment('in="query",', 1, 2);
        $this->setStarredComment('name="sort",', 1, 2);
        $this->setStarredComment('required=false,', 1, 2);
        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES, 1, 1);

        $this->setStarredComment(DocumentationInterface::OA_PARAMETER . PhpInterface::OPEN_PARENTHESES, 1, 1);
        $this->setStarredComment('in="query",', 1, 2);
        $this->setStarredComment('name="data",', 1, 2);
        $this->setStarredComment('required=false,', 1, 2);
        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES, 1, 1);

        $this->setStarredComment(DocumentationInterface::OA_PARAMETER . PhpInterface::OPEN_PARENTHESES, 1, 1);
        $this->setStarredComment('in="query",', 1, 2);
        $this->setStarredComment('name="filter",', 1, 2);
        $this->setStarredComment('required=false,', 1, 2);
        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES, 1, 1);

        $this->setStarredComment(DocumentationInterface::OA_PARAMETER . PhpInterface::OPEN_PARENTHESES, 1, 1);
        $this->setStarredComment('in="query",', 1, 2);
        $this->setStarredComment('name="order_by",', 1, 2);
        $this->setStarredComment('required=false,', 1, 2);
        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES, 1, 1);

        $this->setStarredComment(DocumentationInterface::OA_RESPONSE . PhpInterface::OPEN_PARENTHESES, 1, 1);
        $this->setStarredComment('response=200', 1, 2);
        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES, 1, 1);

        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES);

        $this->closeComment();
    }

    private function setView()
    {
        $this->openComment();

        $this->setStarredComment(DocumentationInterface::OA_GET . PhpInterface::OPEN_PARENTHESES);

        $this->setStarredComment('path="' . PhpInterface::SLASH . $this->generator->version . PhpInterface::SLASH
            . strtolower($this->generator->objectName) . PhpInterface::SLASH . '{id}",', 1, 1);

        $this->setStarredComment('summary="Get ' . $this->generator->objectName . ' source for '
            . MethodsInterface::VIEW . '",', 1, 1);

        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES, 1, 1);

        $this->closeComment();
    }

    private function setCreate()
    {
        $this->openComment();

        $this->setStarredComment(DocumentationInterface::OA_POST . PhpInterface::OPEN_PARENTHESES);

        $this->setStarredComment('path="' . PhpInterface::SLASH . $this->generator->version . PhpInterface::SLASH
            . strtolower($this->generator->objectName) . '",', 1, 1);

        $this->setStarredComment('summary="Create ' . $this->generator->objectName . ' source for '
            . MethodsInterface::CREATE . '",', 1, 1);

        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES, 1, 1);

        $this->closeComment();
    }

    private function setUpdate()
    {
        $this->openComment();

        $this->setStarredComment(DocumentationInterface::OA_PATCH . PhpInterface::OPEN_PARENTHESES);

        $this->setStarredComment('path="' . PhpInterface::SLASH . $this->generator->version . PhpInterface::SLASH
            . strtolower($this->generator->objectName) . '",', 1, 1);

        $this->setStarredComment('summary="Update ' . $this->generator->objectName . ' source for '
            . MethodsInterface::UPDATE . '",', 1, 1);

        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES, 1, 1);

        $this->closeComment();
    }

    private function setDelete()
    {
        $this->openComment();

        $this->setStarredComment(DocumentationInterface::OA_DELETE . PhpInterface::OPEN_PARENTHESES);

        $this->setStarredComment('path="' . PhpInterface::SLASH . $this->generator->version . PhpInterface::SLASH
            . strtolower($this->generator->objectName) . '",', 1, 1);

        $this->setStarredComment('summary="Delete ' . $this->generator->objectName . ' source for '
            . MethodsInterface::DELETE . '",', 1, 1);

        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES, 1, 1);

        $this->closeComment();
    }
}