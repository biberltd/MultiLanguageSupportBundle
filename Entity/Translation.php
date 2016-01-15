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
     * @var int
     */
    private $id;

    /** 
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $domain;

    /** 
     * @ORM\Column(type="string", length=255, nullable=false)
     * @var string
     */
    private $key;

    /** 
     * @ORM\Column(type="datetime", nullable=false)
     * @var \DateTime
     */
    public $date_added;

    /** 
     * @ORM\Column(type="datetime", nullable=false)
     * @var \DateTime
     */
    public $date_updated;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 * @var \DateTime
	 */
	public $date_removed;

    /** 
     * @ORM\OneToMany(
     *     targetEntity="BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\TranslationLocalization",
     *     mappedBy="translation"
     * )
     * @var array
     */
    protected $localizations;

    /** 
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\SiteManagementBundle\Entity\Site")
     * @ORM\JoinColumn(name="site", referencedColumnName="id", onDelete="CASCADE")
     * @var \BiberLtd\Bundle\SiteManagementBundle\Entity\Site
     */
    private $site;

	/**
	 * @return mixed
	 */
    public function getId(){
        return $this->id;
    }

	/**
	 * @param string $key
	 *
	 * @return $this
	 */
    public function setKey(string $key) {
        if(!$this->setModified('key', $key)->isModified()) {
            return $this;
        }
		$this->key = $key;
		return $this;
    }

	/**
	 * @return string
	 */
    public function getKey() {
        return $this->key;
    }

	/**
	 * @param \BiberLtd\Bundle\SiteManagementBundle\Entity\Site $site
	 *
	 * @return $this
	 */
    public function setSite(\BiberLtd\Bundle\SiteManagementBundle\Entity\Site $site) {
        if(!$this->setModified('site', $site)->isModified()) {
            return $this;
        }
		$this->site = $site;
		return $this;
    }

	/**
	 * @return \BiberLtd\Bundle\SiteManagementBundle\Entity\Site
	 */
    public function getSite() {
        return $this->site;
    }

	/**
	 * @param string $domain
	 *
	 * @return $this
	 */
    public function setDomain(string $domain) {
        if(!$this->setModified('domain', $domain)->isModified()) {
            return $this;
        }
        $this->domain = $domain;
        return $this;
    }

	/**
	 * @return string
	 */
    public function getDomain() {
        return $this->domain;
    }
}