<?php
namespace SoliDry\Extension;

use SoliDry\Helpers\ConfigHelper;
use SoliDry\Helpers\MigrationsHelper;
use SoliDry\Types\ConfigInterface;
use SoliDry\Types\PhpInterface;

class SpellCheck
{
    /**
     * @var mixed|null
     */
    private $entity    = [];

    /**
     * @var int|string|null
     */
    private $field     = null;

    /**
     * @var bool
     */
    private bool $isEnabled = false;

    /**
     * @var mixed|string
     */
    private $language  = ConfigInterface::DEFAULT_LANGUAGE;

    /**
     * @var false|int|null
     */
    private $speLink = null;
    /**
     * SpellCheck constructor.
     * @param string $entity
     */
    public function __construct(string $entity)
    {
        $this->entity    = ConfigHelper::getNestedParam(ConfigInterface::SPELL_CHECK, MigrationsHelper::getTableName($entity));
        $this->field     = key($this->entity);
        $this->isEnabled = empty($this->entity[$this->field][ConfigInterface::ENABLED]) ? false : true;
        $this->language  = empty($this->entity[$this->field][ConfigInterface::LANGUAGE]) ? ConfigInterface::DEFAULT_LANGUAGE
            : $this->entity[$this->field][ConfigInterface::LANGUAGE];
        if($this->isEnabled === true) {
            $this->speLink = pspell_new($this->language, '', '', '', PSPELL_FAST);
        }
    }

    /**
     * Gets the "dirty" text and returns array of failed spell checked words
     * if there are any
     * @param string $text
     * @return array
     */
    public function check(string $text)
    {
        $failed = [];
        $cleanText = $this->cleanText($text);
        $words     = explode(PhpInterface::SPACE, $cleanText);
        foreach($words as $k => $word) {
            $pass = pspell_check($this->speLink, $word);
            if($pass === false) {
                $failed[] = $word;
            }
        }
        return $failed;
    }

    /**
     * @param string $text
     * @return string
     */
    private function cleanText(string $text)
    {
        return str_replace([',', '.', '-', '- ', ':', '"'], '', $text);
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @return mixed|null
     */
    public function getField()
    {
        return $this->field;
    }
}