<?php
namespace BiberLtd\Bundle\MultiLanguageSupportBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/** 
 * @ORM\Entity
 * @ORM\Table(
 *     name="translation_localization",
 *     options={"charset":"utf8","collate":"utf8_turkish_ci","engine":"innodb"},
 *     indexes={
 *         @ORM\Index(name="idx_n_translation_localization_date_added", columns={"date_added"}),
 *         @ORM\Index(name="idx_n_translation_localization_date_updated", columns={"date_updated"})
 *     },
 *     uniqueConstraints={@ORM\UniqueConstraint(name="idx_u_translation_localization", columns={"translation","language"})}
 * )
 */
class TranslationLocalization
{
    /** 
     * @ORM\Column(type="text", nullable=false)
     */
    private $phrase;

    /** 
     * @ORM\Column(type="datetime", nullable=false)
     */
    public $date_added;

    /** 
     * @ORM\Column(type="datetime", nullable=false)
     */
    public $date_updated;

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
     * @ORM\JoinColumn(name="translation", referencedColumnName="id", onDelete="CASCADE")
     */
    private $translation;

    /**
     * @name                  setLanguage ()
     *                                    Sets the language property.
     *                                    Updates the data only if stored value and value to be set are different.
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
     *                              Returns the value of language property.
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
     * @name                  setPhrase ()
     *                                  Sets the phrase property.
     *                                  Updates the data only if stored value and value to be set are different.
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
     *                            Returns the value of phrase property.
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
     * @name                  setTranslation ()
     *                                       Sets the translation property.
     *                                       Updates the data only if stored value and value to be set are different.
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
     *                                 Returns the value of translation property.
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