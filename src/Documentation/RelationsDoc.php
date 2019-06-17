<?php

namespace SoliDry\Documentation;

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
            . strtolower($this->generator->objectName) . PhpInterface::SLASH . '{id}/{related}",', 1, 1);

        $this->setStarredComment('summary="Get ' . $this->generator->objectName . ' related objects",', 1, 1);

        $this->setStarredComment('tags={"' . $this->generator->objectName . DefaultInterface::CONTROLLER_POSTFIX
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
            'response'    => '200',
            'description' => '""',
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
            . strtolower($this->generator->objectName) . PhpInterface::SLASH . '{id}/relationships/{relations}",', 1, 1);

        $this->setStarredComment('summary="Get ' . $this->generator->objectName . ' relations objects",', 1, 1);

        $this->setStarredComment('tags={"' . $this->generator->objectName . DefaultInterface::CONTROLLER_POSTFIX
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
            'response'    => '200',
            'description' => '""',
        ]);

        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES);

        $this->closeComment();
        $this->setNewLines();
    }
}