<?php
namespace rjapi\extension;

use rjapi\types\ConfigInterface;

trait SpellCheckTrait
{
    /**
     * Field spell checker
     * @param array $jsonProps
     * @return array
     */
    protected function spellCheck(array $jsonProps)
    {
        $arr = [];
        $spellCheck = new SpellCheck($this->entity);
        $field = $spellCheck->getField();
        if($spellCheck->isEnabled() === true) {
            $arr = $spellCheck->check($jsonProps[$field]);
        }
        return [ConfigInterface::SPELL_CHECK => [$field => $arr]];
    }
}