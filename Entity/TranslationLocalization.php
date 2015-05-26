<?php
/**
 * @name        TranslationLocalization
 * @package		BiberLtd\Bundle\CoreBundle\MultiLanguageSupportBundle
 *
 * @author		Can Berkol
 * @version     1.0.2
 * @date        26.05.2015
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com)
 * @license     GPL v3.0
 *
 * @description Model / Entity class.
 *
 */
namespace BiberLtd\Bundle\MultiLanguageSupportBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="translation_localization",
 *     options={"charset":"utf8","collate":"utf8_turkish_ci","engine":"innodb"},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="idxUTranslationLocalization", columns={"translation","language"})}
 * )
 */
class TranslationLocalization
{
	/**
	 * @ORM\Column(type="text", nullable=false)
	 */
	private $phrase;

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Language")
	 * @ORM\JoinColumn(name="language", referencedColumnName="id", nullable=false, onDelete="CASCADE")
	 */
	private $language;

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(
	 *     targetEntity="BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Translation",
	 *     inversedBy="localizations"
	 * )
	 * @ORM\JoinColumn(name="translation", referencedColumnName="id", nullable=false, onDelete="CASCADE")
	 */
	private $translation;

	/**
	 * @name            setLanguage ()
	 *
	 * @author          Can Berkol
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @use             $this->setModified()
	 *
	 * @param           mixed $language
	 *
	 * @return          object                $this
	 */
	public function setLanguage($language) {
		if(!$this->setModified('language', $language)->isModified()) {
			return $this;
		}
		$this->language = $language;
		return $this;
	}

	/**
	 * @name            getLanguage ()
	 *
	 * @author          Can Berkol
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @return          mixed           $this->language
	 */
	public function getLanguage() {
		return $this->language;
	}

	/**
	 * @name            setPhrase ()
	 *
	 * @author          Can Berkol
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @use             $this->setModified()
	 *
	 * @param           mixed $phrase
	 *
	 * @return          object                $this
	 */
	public function setPhrase($phrase) {
		if(!$this->setModified('phrase', $phrase)->isModified()) {
			return $this;
		}
		$this->phrase = $phrase;
		return $this;
	}

	/**
	 * @name            getPhrase ()
	 *
	 * @author          Can Berkol
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @return          mixed           $this->phrase
	 */
	public function getPhrase() {
		return $this->phrase;
	}

	/**
	 * @name            setTranslation ()
	 *
	 * @author          Can Berkol
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @use             $this->setModified()
	 *
	 * @param           mixed $translation
	 *
	 * @return          object                $this
	 */
	public function setTranslation($translation) {
		if(!$this->setModified('translation', $translation)->isModified()) {
			return $this;
		}
		$this->translation = $translation;
		return $this;
	}

	/**
	 * @name            getTranslation ()
	 *
	 * @author          Can Berkol
	 *
	 * @since           1.0.0
	 * @version         1.0.0
	 *
	 * @return          mixed           $this->translation
	 */
	public function getTranslation() {
		return $this->translation;
	}
}
/**
 * Change Log
 * v1.0.2                      26.05.2015
 * Can Berkol
 * **************************************
 * BF :: Namespaces in annotations fixed.
 *
 * **************************************
 * v1.0.1                      30.04.2015
 * TW #
 * Can Berkol
 * **************************************
 * ORM Changes.
 */