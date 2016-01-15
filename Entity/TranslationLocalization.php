<?php
/**
 * @author		Can Berkol
 * @author		Said İmamoğlu
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com) (C) 2015
 * @license     GPLv3
 *
 * @date        23.12.2015
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
	 * @var string
	 */
	private $phrase;

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Language")
	 * @ORM\JoinColumn(name="language", referencedColumnName="id", nullable=false, onDelete="CASCADE")
	 * @var \BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Language
	 */
	private $language;

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(
	 *     targetEntity="BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Translation",
	 *     inversedBy="localizations"
	 * )
	 * @ORM\JoinColumn(name="translation", referencedColumnName="id", nullable=false, onDelete="CASCADE")
	 * @var \BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Translation
	 */
	private $translation;

	/**
	 * @param \BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Language $language
	 *
	 * @return $this
	 */
	public function setLanguage(\BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Language $language) {
		if(!$this->setModified('language', $language)->isModified()) {
			return $this;
		}
		$this->language = $language;
		return $this;
	}

	/**
	 * @return \BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Language
	 */
	public function getLanguage() {
		return $this->language;
	}

	/**
	 * @param string $phrase
	 *
	 * @return $this
	 */
	public function setPhrase(string $phrase) {
		if(!$this->setModified('phrase', $phrase)->isModified()) {
			return $this;
		}
		$this->phrase = $phrase;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPhrase() {
		return $this->phrase;
	}

	/**
	 * @param \BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Translation $translation
	 *
	 * @return $this
	 */
	public function setTranslation(\BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Translation $translation) {
		if(!$this->setModified('translation', $translation)->isModified()) {
			return $this;
		}
		$this->translation = $translation;
		return $this;
	}

	/**
	 * @return \BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Translation
	 */
	public function getTranslation() {
		return $this->translation;
	}
}