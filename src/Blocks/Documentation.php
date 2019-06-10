<?php

namespace SoliDry\Blocks;

use SoliDry\ApiGenerator;
use SoliDry\Controllers\BaseCommand;
use SoliDry\Helpers\Classes;
use SoliDry\Types\ApiInterface;
use SoliDry\Types\DefaultInterface;
use SoliDry\Types\DocumentationInterface;
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
}