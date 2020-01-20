<?php

namespace SoliDry\Documentation;

use SoliDry\Extension\JSONApiInterface;
use SoliDry\Helpers\Classes;
use SoliDry\Helpers\MigrationsHelper;
use SoliDry\Types\DefaultInterface;
use SoliDry\Types\DocumentationInterface;
use SoliDry\Types\PhpInterface;

trait BulksDoc
{
    /**
     *  Sets OAS documentation for a bulk create method
     */
    private function setCreateBulk(): void
    {
        $this->openComment();

        $this->setStarredComment(DocumentationInterface::OA_POST . PhpInterface::OPEN_PARENTHESES);
        $this->setStarredComment('path="' . PhpInterface::SLASH . $this->generator->version . PhpInterface::SLASH
            . MigrationsHelper::getTableName($this->generator->objectName) . PhpInterface::SLASH . 'bulk",', 1, 1);

        $this->setStarredComment('summary="Create ' . Classes::getClassName($this->generator->objectName) . ' bulk",', 1, 1);

        $this->setStarredComment('tags={"' . Classes::getClassName($this->generator->objectName) . DefaultInterface::CONTROLLER_POSTFIX
            . '"},', 1, 1);

        $this->setResponse([
            'response'    => '"' . JSONApiInterface::HTTP_RESPONSE_CODE_CREATED . '"',
            'description' => '"' . self::SUCCESSFUL_OPERATION . '"',
        ]);

        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES);

        $this->closeComment();
        $this->setNewLines();
    }

    /**
     *  Sets OAS documentation for a bulk update method
     */
    private function setUpdateBulk(): void
    {
        $this->openComment();

        $this->setStarredComment(DocumentationInterface::OA_PATCH . PhpInterface::OPEN_PARENTHESES);

        $this->setStarredComment('path="' . PhpInterface::SLASH . $this->generator->version . PhpInterface::SLASH
            . MigrationsHelper::getTableName($this->generator->objectName) . PhpInterface::SLASH . 'bulk",', 1, 1);

        $this->setStarredComment('summary="Update ' . Classes::getClassName($this->generator->objectName) . ' bulk",', 1, 1);

        $this->setStarredComment('tags={"' . Classes::getClassName($this->generator->objectName) . DefaultInterface::CONTROLLER_POSTFIX
            . '"},', 1, 1);

        $this->setResponse([
            'response'    => '"' . JSONApiInterface::HTTP_RESPONSE_CODE_OK . '"',
            'description' => '"' . self::SUCCESSFUL_OPERATION . '"',
        ]);

        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES);

        $this->closeComment();
        $this->setNewLines();
    }

    /**
     *  Sets OAS documentation for a bulk delete method
     */
    private function setDeleteBulk(): void
    {
        $this->openComment();

        $this->setStarredComment(DocumentationInterface::OA_DELETE . PhpInterface::OPEN_PARENTHESES);

        $this->setStarredComment('path="' . PhpInterface::SLASH . $this->generator->version . PhpInterface::SLASH
            . MigrationsHelper::getTableName($this->generator->objectName) . PhpInterface::SLASH . 'bulk",', 1, 1);

        $this->setStarredComment('summary="Delete ' . Classes::getClassName($this->generator->objectName) . ' bulk",', 1, 1);

        $this->setStarredComment('tags={"' . Classes::getClassName($this->generator->objectName) . DefaultInterface::CONTROLLER_POSTFIX
            . '"},', 1, 1);

        $this->setResponse([
            'response'    => '"' . JSONApiInterface::HTTP_RESPONSE_CODE_NO_CONTENT . '"',
            'description' => '"' . self::SUCCESSFUL_OPERATION . '"',
        ]);

        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES);

        $this->closeComment();
        $this->setNewLines();
    }
}