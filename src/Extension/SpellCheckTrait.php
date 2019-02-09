<?php
namespace SoliDry\Extension;

use SoliDry\Types\ConfigInterface;
use SoliDry\Types\PhpInterface;

/**
 * Class SpellCheckTrait
 *
 * @package SoliDry\Extension
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
    protected function spellCheck(array $jsonProps) : array
    {
        if (false === extension_loaded(PhpInterface::PHP_EXTENSION_PSPELL)) {
            return [ConfigInterface::SPELL_CHECK => 'php-pspell library has not been installed on Your system.'];
        }
        $arr        = [];
        $spellCheck = new SpellCheck($this->entity);
        $field      = $spellCheck->getField();
        if (true === $spellCheck->isEnabled()) {
            $arr = $spellCheck->check($jsonProps[$field]);
        }

        return [ConfigInterface::SPELL_CHECK => [$field => $arr]];
    }
}