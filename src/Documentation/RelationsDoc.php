<?php

namespace SoliDry\Documentation;

use SoliDry\Extension\JSONApiInterface;
use SoliDry\Helpers\Classes;
use SoliDry\Helpers\MigrationsHelper;
use SoliDry\Types\DefaultInterface;
use SoliDry\Types\DocumentationInterface;
use SoliDry\Types\PhpInterface;

trait RelationsDoc
{
    /**
     *  Sets OAS documentation for a related method
     */
    private function setRelated(): void
    {
        $this->openComment();

        $this->setStarredComment(DocumentationInterface::OA_GET . PhpInterface::OPEN_PARENTHESES);

        $this->setStarredComment('path="' . PhpInterface::SLASH . $this->generator->version . PhpInterface::SLASH
            . MigrationsHelper::getTableName($this->generator->objectName) . PhpInterface::SLASH . '{id}/{related}",', 1, 1);

        $this->setStarredComment('summary="Get ' . Classes::getClassName($this->generator->objectName) . ' related objects",', 1, 1);

        $this->setStarredComment('tags={"' . Classes::getClassName($this->generator->objectName) . DefaultInterface::CONTROLLER_POSTFIX
            . '"},', 1, 1);

        $this->setParameter([
            'in'       => '"query"',
            'name'     => '"data"',
            'required' => 'false',
        ]);

        $this->setParameter([
            'in'       => '"path"',
            'name'     => '"id"',
            'required' => 'true',
        ]);

        $this->setParameter([
            'in'       => '"path"',
            'name'     => '"related"',
            'required' => 'true',
        ]);

        $this->setResponse([
            'response'    => '"' . JSONApiInterface::HTTP_RESPONSE_CODE_OK . '"',
            'description' => '"' . self::SUCCESSFUL_OPERATION . '"',
        ]);

        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES);

        $this->closeComment();
        $this->setNewLines();
    }

    /**
     *  Sets OAS documentation for getting relations method
     */
    private function setRelations(): void
    {
        $this->openComment();

        $this->setStarredComment(DocumentationInterface::OA_GET . PhpInterface::OPEN_PARENTHESES);

        $this->setStarredComment('path="' . PhpInterface::SLASH . $this->generator->version . PhpInterface::SLASH
            . MigrationsHelper::getTableName($this->generator->objectName) . PhpInterface::SLASH . '{id}/relationships/{relations}",', 1, 1);

        $this->setStarredComment('summary="Get ' . Classes::getClassName($this->generator->objectName) . ' relations objects",', 1, 1);

        $this->setStarredComment('tags={"' . Classes::getClassName($this->generator->objectName) . DefaultInterface::CONTROLLER_POSTFIX
            . '"},', 1, 1);

        $this->setParameter([
            'in'       => '"query"',
            'name'     => '"data"',
            'required' => 'false',
        ]);

        $this->setParameter([
            'in'       => '"path"',
            'name'     => '"id"',
            'required' => 'true',
        ]);

        $this->setParameter([
            'in'       => '"path"',
            'name'     => '"relations"',
            'required' => 'true',
        ]);

        $this->setResponse([
            'response'    => '"' . JSONApiInterface::HTTP_RESPONSE_CODE_OK . '"',
            'description' => '"' . self::SUCCESSFUL_OPERATION . '"',
        ]);

        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES);

        $this->closeComment();
        $this->setNewLines();
    }

    /**
     *  Sets OAS documentation for creating relation method
     */
    private function setCreateRelation(): void
    {
        $this->openComment();

        $this->setStarredComment(DocumentationInterface::OA_POST . PhpInterface::OPEN_PARENTHESES);

        $this->setStarredComment('path="' . PhpInterface::SLASH . $this->generator->version . PhpInterface::SLASH
            . MigrationsHelper::getTableName($this->generator->objectName) . PhpInterface::SLASH . '{id}/relationships/{relations}",', 1, 1);

        $this->setStarredComment('summary="Create ' . Classes::getClassName($this->generator->objectName) . ' relation object",', 1, 1);

        $this->setStarredComment('tags={"' . Classes::getClassName($this->generator->objectName) . DefaultInterface::CONTROLLER_POSTFIX
            . '"},', 1, 1);

        $this->setParameter([
            'in'       => '"path"',
            'name'     => '"id"',
            'required' => 'true',
        ]);

        $this->setParameter([
            'in'       => '"path"',
            'name'     => '"relations"',
            'required' => 'true',
        ]);

        $this->setResponse([
            'response'    => '"' . JSONApiInterface::HTTP_RESPONSE_CODE_CREATED . '"',
            'description' => '"' . self::SUCCESSFUL_OPERATION . '"',
        ]);

        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES);

        $this->closeComment();
        $this->setNewLines();
    }

    /**
     *  Sets OAS documentation for updating relation method
     */
    private function setUpdateRelation(): void
    {
        $this->openComment();

        $this->setStarredComment(DocumentationInterface::OA_PATCH . PhpInterface::OPEN_PARENTHESES);

        $this->setStarredComment('path="' . PhpInterface::SLASH . $this->generator->version . PhpInterface::SLASH
            . MigrationsHelper::getTableName($this->generator->objectName) . PhpInterface::SLASH . '{id}/relationships/{relations}",', 1, 1);

        $this->setStarredComment('summary="Update ' . Classes::getClassName($this->generator->objectName) . ' relation object",', 1, 1);

        $this->setStarredComment('tags={"' . Classes::getClassName($this->generator->objectName) . DefaultInterface::CONTROLLER_POSTFIX
            . '"},', 1, 1);

        $this->setParameter([
            'in'       => '"path"',
            'name'     => '"id"',
            'required' => 'true',
        ]);

        $this->setParameter([
            'in'       => '"path"',
            'name'     => '"relations"',
            'required' => 'true',
        ]);

        $this->setResponse([
            'response'    => '"' . JSONApiInterface::HTTP_RESPONSE_CODE_OK . '"',
            'description' => '"' . self::SUCCESSFUL_OPERATION . '"',
        ]);

        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES);

        $this->closeComment();
        $this->setNewLines();
    }

    /**
     *  Sets OAS documentation for deleting relation method
     */
    private function setDeleteRelation(): void
    {
        $this->openComment();

        $this->setStarredComment(DocumentationInterface::OA_DELETE . PhpInterface::OPEN_PARENTHESES);

        $this->setStarredComment('path="' . PhpInterface::SLASH . $this->generator->version . PhpInterface::SLASH
            . MigrationsHelper::getTableName($this->generator->objectName) . PhpInterface::SLASH . '{id}/relationships/{relations}",', 1, 1);

        $this->setStarredComment('summary="Delete ' . Classes::getClassName($this->generator->objectName) . ' relation object",', 1, 1);

        $this->setStarredComment('tags={"' . Classes::getClassName($this->generator->objectName) . DefaultInterface::CONTROLLER_POSTFIX
            . '"},', 1, 1);

        $this->setParameter([
            'in'       => '"path"',
            'name'     => '"id"',
            'required' => 'true',
        ]);

        $this->setParameter([
            'in'       => '"path"',
            'name'     => '"relations"',
            'required' => 'true',
        ]);

        $this->setResponse([
            'response'    => '"' . JSONApiInterface::HTTP_RESPONSE_CODE_NO_CONTENT . '"',
            'description' => '"' . self::SUCCESSFUL_OPERATION . '"',
        ]);

        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES);

        $this->closeComment();
        $this->setNewLines();
    }
}