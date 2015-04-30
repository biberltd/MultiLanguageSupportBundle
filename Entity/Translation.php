<?php
namespace BiberLtd\Bundle\MultiLanguageSupportBundle\Entity;
/**
 * @name        Translation
 * @package		BiberLtd\Bundle\CoreBundle\MultiLanguageSupportBundle
 *
 * @author		Can Berkol
 * @version     1.0.1
 * @date        30.04.2015
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com)
 * @license     GPL v3.0
 *
 * @description Model / Entity class.
 *
 */
use BiberLtd\Bundle\CoreBundle\CoreLocalizableEntity;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="translation",
 *     options={"charset":"utf8","collate":"utf8_turkish_ci","engine":"innodb"},
 *     indexes={
 *         @ORM\Index(name="idxNTranslationDateAdded", columns={"date_added"}),
 *         @ORM\Index(name="idxNTranslationDateUpdated", columns={"date_updated"}),
 *         @ORM\Index(name="idxNTranslationDateRemoved", columns={"date_removed"})
 *     },
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="idxUTranslationId", columns={"id"}),
 *         @ORM\UniqueConstraint(name="idxUTranslationKey", columns={"key","site"})
 *     }
 * )
 */
class Translation extends CoreLocalizableEntity
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", length=20)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** 
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $domain;

    /** 
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $key;

    /** 
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $instructions;

    /** 
     * @ORM\Column(type="datetime", nullable=false)
     */
    public $date_added;

    /** 
     * @ORM\Column(type="datetime", nullable=false)
     */
    public $date_updated;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	public $date_removed;

    /** 
     * @ORM\OneToMany(
     *     targetEntity="BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\TranslationLocalization",
     *     mappedBy="translation"
     * )
     */
    protected $localizations;

    /** 
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\SiteManagementBundle\Entity\Site")
     * @ORM\JoinColumn(name="site", referencedColumnName="id", onDelete="CASCADE")
     */
    private $site;

    /******************************************************************
     * PUBLIC SET AND GET FUNCTIONS                                   *
     ******************************************************************/

    /**
     * @name            getId()
	 *
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          string          $this->id
     */
    public function getId(){
        return $this->id;
    }

    /**
     * @name            setKey ()
	 *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $key
     *
     * @return          object                $this
     */
    public function setKey($key) {
        if(!$this->setModified('key', $key)->isModified()) {
            return $this;
        }
		$this->key = $key;
		return $this;
    }

    /**
     * @name            getKey ()
     *                  Returns the value of key property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->key
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * @name            setSite ()
	 *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $site
     *
     * @return          object                $this
     */
    public function setSite($site) {
        if(!$this->setModified('site', $site)->isModified()) {
            return $this;
        }
		$this->site = $site;
		return $this;
    }

    /**
     * @name            getSite ()
	 *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->site
     */
    public function getSite() {
        return $this->site;
    }

    /**
     * @name            setInstructions()
	 *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $instructions
     *
     * @return          object                $this
     */
    public function setInstructions($instructions) {
        if(!$this->setModified('instructions', $instructions)->isModified()) {
            return $this;
        }
		$this->instructions = $instructions;
		return $this;
    }

    /**
     * @name            getInstructions()
	 *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->instructions
     */
    public function getInstructions() {
        return $this->instructions;
    }
    /**
     * @name            setDomain()
	 *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           string $domain
     *
     * @return          object                $this
     */
    public function setDomain($domain) {
        if(!$this->setModified('domain', $domain)->isModified()) {
            return $this;
        }
        $this->domain = $domain;
        return $this;
    }

    /**
     * @name            getDomain()
	 *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->instructions
     */
    public function getDomain() {
        return $this->domain;
    }
}
/**
 * Change Log:
 * **************************************
 * v1.0.1                      30.04.2015
 * TW #
 * Can Berkol
 * **************************************
 * ORM Changes.
 *
 * **************************************
 * v1.0.0                      Can Berkol
 * 27.03.2014
 * **************************************
 * A getDomain()
 * A getId()
 * A getInstructions()
 * A getKey()
 * A getSite()
 * A setDomain()
 * A setInstructions()
 * A setKey()
 * A setSite()
 */