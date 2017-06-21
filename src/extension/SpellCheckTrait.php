<?php
namespace rjapi\extension;

use rjapi\types\ConfigInterface;
use rjapi\types\PhpInterface;

/**
 * Class SpellCheckTrait
 *
 * @package rjapi\extension
 *
 * @property ApiController entity
 */
trait SpellCheckTrait
{
    /**
     * Field spell checker
     *
     * @param array $jsonProps
     *
     * @return array
     */
    protected function spellCheck(array $jsonProps)
    {
        $arr = [];
        if (false === extension_loaded(PhpInterface::PHP_EXTENSION_PSPELL)) {
            return [ConfigInterface::SPELL_CHECK => 'php-pspell library has not been installed on Your system.'];
        }
        $spellCheck = new SpellCheck($this->entity);
        $field      = $spellCheck->getField();
        if (true === $spellCheck->isEnabled()) {
            $arr = $spellCheck->check($jsonProps[$field]);
        }

        return [ConfigInterface::SPELL_CHECK => [$field => $arr]];
    }
}